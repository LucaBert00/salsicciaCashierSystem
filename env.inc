<?php
declare(strict_types=1);
// Carica .env (KEY=VALORE) nelle env di processo; le env reali (sistema/Apache) hanno precedenza.
$f = __DIR__ . '/.env';
if (is_readable($f))
{
    foreach ((array)@parse_ini_file($f, false, INI_SCANNER_RAW) as $k => $v)
    {
        if (getenv($k) === false)
        {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

// Cartella fuori docroot per spool/coda/log (T16: DocumentRoot -> public/).
// env.inc resta alla radice (fuori docroot), quindi __DIR__ e' sempre la radice.
function salsiccia_storage_dir(): string
{
    $d = __DIR__ . '/storage';
    if (!is_dir($d))
        @mkdir($d, 0770, true);
    return $d;
}

// Path assoluto in storage/ per nomi spool/coda/log (solo basename, mai traversal).
function salsiccia_storage_path(string $name): string
{
    return salsiccia_storage_dir() . '/' . basename($name);
}

// Gate stampa etichette (issue #54): unico punto di definizione della
// tabella di verita' stampante/connessione/linguaggio. Usato sia dal gate
// stampa_permessa in genera_file_stampa() (funzioni.inc) sia dal calcolo
// di CONTINUOUS_LABEL (set.inc): stessa tabella, nessun duplicato.
function printer_gate_allowed($name, $conn, $lang)
{
    if ($name === 'Zebra_Multi')
        return $conn === 'DIRETTA' && ($lang === 'ZPL' || $lang === 'EPL');
    if ($name === 'Zebra_Net')
        return ($conn === 'DIRETTA' || $conn === 'RETE') && $lang === 'ZPL';
    if ($name === 'Zebra_EPL_1' || $name === 'Zebra_EPL_2')
        return $conn === 'DIRETTA' && $lang === 'EPL';
    return false;
}

// Carta continua (scontrino unico) vs biglietti singoli: Zebra_Multi in
// DIRETTA e Zebra_Net (RETE/DIRETTA ZPL) fanno scontrino unico su richiesta
// deploy; Zebra_EPL_1/2 fanno singoli. Deploy attuale: Zebra_Net continua.
function printer_is_continuous($name, $conn, $lang)
{
    return printer_gate_allowed($name, $conn, $lang) && ($name === 'Zebra_Multi' || $name === 'Zebra_Net');
}

// T17: flag fiera persistente in storage/ JSON, mai rewrite di PHP source.
// Unico punto di verita' per MODALITA_FIERA: letto da isFieraAttiva()
// (functionsFrontend.inc) e fieraAttiva() (public/reserved/visualizza.php),
// scritto da impostaModalitaFiera(). Scrittura atomica tmp+rename con LOCK_EX:
// concorrente = last-writer-wins, mai file troncato ne' sorgente toccato.
// Fallback a config/cassa.php inerte poi a set.inc congelato (solo default).
function cassa_flags_file(): string
{
    return salsiccia_storage_path('cassa_flags.json');
}

function cassa_leggi_fiera(): bool
{
    $raw = @file_get_contents(cassa_flags_file());
    if (is_string($raw) && $raw !== '')
    {
        $j = json_decode($raw, true);
        if (is_array($j) && isset($j['modalita_fiera']))
            return $j['modalita_fiera'] === '1' || $j['modalita_fiera'] === 1 || $j['modalita_fiera'] === true;
    }
    $cfg = __DIR__ . '/config/cassa.php';
    if (is_readable($cfg))
    {
        $arr = @include $cfg;
        if (is_array($arr) && isset($arr['MODALITA_FIERA']))
            return (string)$arr['MODALITA_FIERA'] === '1';
    }
    return defined('MODALITA_FIERA') && MODALITA_FIERA === '1';
}

// ponytail: tmp+rename atomico, per-process tmp per concorrenza
function cassa_imposta_fiera($valore): void
{
    $v = $valore === '1' || $valore === 1 || $valore === true ? '1' : '0';
    $f = cassa_flags_file();
    $tmp = $f . '.' . getmypid() . '.tmp';
    $json = json_encode(array('modalita_fiera' => $v));
    if (!is_string($json))
        return;
    if (@file_put_contents($tmp, $json, LOCK_EX) === false)
        return;
    @rename($tmp, $f);
}
?>
