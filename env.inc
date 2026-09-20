<?php
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
?>
