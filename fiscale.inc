<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successore pianificato src/Fiscale/Fiscale.php (§7); rimozione solo dopo la migrazione, rivalutare entro 2026-12-31.
if (!function_exists('cassa_log'))
    require_once __DIR__ . '/env.inc';
require_once __DIR__ . '/src/Cassa/PayMethod.php';

// Scontrino fiscale via registratore Print!F RT (mappa #87, Decide #88, Task #92).
// Protocollo Custom su /service.cgi (research #89): POST form data=<Service> con
// =K apertura, =R1/R2/R3 reparti IVA 4/10/22 in centesimi, =T1 contanti /=T3 carte;
// risposta XML con errorCode, printerError, paperEnd, coverOpen, lastCmd, busy.
// La cassa non si blocca mai: ogni errore/timeout finisce in coda e la vendita
// prosegue. Hardening #97: trasporto isolato in fiscale_trasmetti (mai code),
// retry limitato via fiscale_ritenta_coda (anche in automatico dopo un invio
// riuscito, mai quando il registratore e' giu), cassa_log solo con codici.
// Test senza hardware via mock su file + sender iniettabile nel retry.
// Env (mai IP fisso in codice, mai credenziali: il protocollo non ne usa):
//   SALSICCIA_FISCALE_IP (vuoto = fiscale disabilitato, nessun default)
//   SALSICCIA_FISCALE_CONNECT_TIMEOUT (default 2), SALSICCIA_FISCALE_TIMEOUT (default 9)
//   SALSICCIA_FISCALE_MOCK=1 (scrive l'XML su file invece della rete)
//   SALSICCIA_FISCALE_MOCK_FILE / _QUEUE_FILE / _SENT_FILE (nomi file o path assoluti)

// Config da env, con default del Decide #88 (connect 2s, totale 9s).
function fiscale_config(): array
{
    $ip = trim((string)(getenv('SALSICCIA_FISCALE_IP') ?: ''));
    $ct = (int)(getenv('SALSICCIA_FISCALE_CONNECT_TIMEOUT') ?: 2);
    $to = (int)(getenv('SALSICCIA_FISCALE_TIMEOUT') ?: 9);
    return array(
        'ip' => $ip,
        'connect_timeout' => max(1, min(10, $ct)),
        'timeout' => max(1, min(30, $to)),
        'mock' => getenv('SALSICCIA_FISCALE_MOCK') === '1',
        'mock_file' => fiscale_path(getenv('SALSICCIA_FISCALE_MOCK_FILE') ?: 'fiscale_mock.xml'),
        'queue_file' => fiscale_path(getenv('SALSICCIA_FISCALE_QUEUE_FILE') ?: 'fiscale_coda.log'),
        'sent_file' => fiscale_path(getenv('SALSICCIA_FISCALE_SENT_FILE') ?: 'fiscale_inviati.log'),
    );
}

// Path assoluto consentito (test/mock fuori docroot) o nome file in storage/
// (fuori docroot da T16: DocumentRoot -> public/, mai piu in docroot).
function fiscale_path(string $v): string
{
    if (!is_dir(__DIR__ . '/storage'))
        mkdir(__DIR__ . '/storage', 0770, true);
    $v = trim($v);
    if ($v === '')
        return __DIR__ . '/storage/fiscale_coda.log';
    if (preg_match('#^[A-Za-z]:[\\\\/]#', $v) || $v[0] === '/')
        return $v;
    return __DIR__ . '/storage/' . basename($v);
}

// Builder puro su righe aggregate (iva, totale): testabile senza DB ne' rete.
// $righe = lista assoc con 'iva' (float) e 'totale' (float). Ritorna '' se niente da fiscalizzare.
// T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato, mai default silenzioso).
function fiscale_build_xml(array $righe, string $metodo): string
{
    $map = array('0.04' => 'R1', '0.10' => 'R2', '0.22' => 'R3');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n<Service><cmd>=K</cmd>";
    $n = 0;
    foreach ($righe as $riga)
    {
        $k = number_format((float)($riga['iva'] ?? 0), 2, '.', '');
        $cent = (int)round((float)($riga['totale'] ?? 0) * 100);
        if (!isset($map[$k]) || $cent <= 0)
            continue;
        $xml .= "\n<cmd>=" . $map[$k] . "/\$" . $cent . "</cmd>";
        $n++;
    }
    if ($n === 0)
        return '';
    $pay = \Salsiccia\Cassa\PayMethod::tryFrom($metodo);
    if ($pay === null)
        return '';
    $xml .= ($pay === \Salsiccia\Cassa\PayMethod::Carta) ? "\n<cmd>=T3</cmd>" : "\n<cmd>=T1</cmd>";
    return $xml . "\n</Service>";
}

// Parser puro della risposta a 6 campi. Mai eccezioni: illeggibile = non-ok.
function fiscale_parse_stato(string $resp): array
{
    $st = array('ok' => false, 'errorCode' => -1, 'printerError' => -1, 'paperEnd' => -1, 'coverOpen' => -1, 'busy' => -1);
    $prev = libxml_use_internal_errors(true);
    $x = simplexml_load_string($resp);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    if ($x === false || !isset($x->Request))
        return $st;
    foreach (array('errorCode', 'printerError', 'paperEnd', 'coverOpen', 'busy') as $f)
        $st[$f] = (int)$x->Request->$f;
    $st['ok'] = ($st['errorCode'] === 0 && $st['printerError'] === 0 && $st['paperEnd'] === 0 && $st['coverOpen'] === 0 && $st['busy'] === 0);
    return $st;
}

// Trasporto puro: un solo tentativo verso il registratore, mai code, mai eccezioni.
// Ritorna ['ok'=>true,'mock'=>true] | ['ok'=>true,'stato'=>...]
// oppure ['ok'=>false,'motivo'=>...,'log'=>riga gia' sanificata per cassa_log].
// 'log' contiene solo codici di stato, mai XML ne' dati del documento.
function fiscale_trasmetti(string $xml, array $cfg): array
{
    if (!empty($cfg['mock']))
    {
        if (file_put_contents((string)$cfg['mock_file'], $xml . "\n", FILE_APPEND | LOCK_EX) === false)
            cassa_log('warning', 'fiscale mock scrittura fallita');
        return array('ok' => true, 'mock' => true);
    }
    $ip = (string)($cfg['ip'] ?? '');
    if ($ip === '' || (!filter_var($ip, FILTER_VALIDATE_IP) && !preg_match('/^[A-Za-z0-9.-]+$/', $ip)))
        return array('ok' => false, 'motivo' => 'non configurato', 'log' => '');
    $ch = curl_init('http://' . $ip . '/service.cgi');
    if ($ch === false)
        return array('ok' => false, 'motivo' => 'curl_init', 'log' => 'fiscale: registratore non risponde (curl_init), coda');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $xml));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int)$cfg['connect_timeout']);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)$cfg['timeout']);
    $resp = curl_exec($ch);
    $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if (!is_string($resp) || $resp === '' || $http !== 200)
        return array('ok' => false, 'motivo' => 'http ' . $http, 'log' => 'fiscale: registratore non risponde (http=' . $http . ($cerr !== '' ? ' curl=' . $cerr : '') . '), coda');
    $st = fiscale_parse_stato($resp);
    if (!$st['ok'])
        return array('ok' => false, 'motivo' => 'errorCode ' . $st['errorCode'], 'log' => 'fiscale: scarto registratore errorCode=' . $st['errorCode'] . ' paperEnd=' . $st['paperEnd'] . ' coverOpen=' . $st['coverOpen'] . ' busy=' . $st['busy'] . ', coda');
    return array('ok' => true, 'stato' => $st);
}

// Invio con timeout + fallback in coda. Non lancia mai: ritorna sempre un esito.
// Esiti: ['ok'=>true] | ['ok'=>true,'mock'=>true] | ['ok'=>false,'skipped'=>true] | ['ok'=>false,'fallback'=>true]
// T26: $metodo validato via PayMethod enum; ignoto = skipped (rifiutato).
function fiscale_invia(string $xml, array $cfg, int $id_ordine = 0, string $metodo = ''): array
{
    if ($xml === '')
        return array('ok' => false, 'skipped' => true, 'msg' => 'niente da fiscalizzare');
    if (\Salsiccia\Cassa\PayMethod::tryFrom($metodo) === null)
        return array('ok' => false, 'skipped' => true, 'msg' => 'metodo non valido');
    $t = fiscale_trasmetti($xml, $cfg);
    if (empty($t['ok']))
    {
        if (($t['motivo'] ?? '') === 'non configurato')
            return array('ok' => false, 'skipped' => true, 'msg' => 'fiscale non configurato');
        if (!empty($t['log']))
            cassa_log('warning', (string)$t['log']);
        return fiscale_fallback($cfg, $id_ordine, $metodo, $xml, (string)($t['motivo'] ?? 'trasmissione'));
    }
    unset($t['motivo'], $t['log']);
    return $t;
}

// Accoda il documento in attesa (una riga JSON). Solo dati del documento, niente segreti.
function fiscale_fallback(array $cfg, int $id_ordine, string $metodo, string $xml, string $motivo): array
{
    $riga = json_encode(array('ts' => date('c'), 'id_ordine' => $id_ordine, 'metodo' => $metodo, 'motivo' => $motivo, 'xml' => $xml));
    if (is_string($riga) && file_put_contents((string)$cfg['queue_file'], $riga . "\n", FILE_APPEND | LOCK_EX) === false)
        cassa_log('error', 'fiscale coda scrittura fallita id_ordine=' . $id_ordine);
    return array('ok' => false, 'fallback' => true, 'msg' => $motivo);
}

// Retry limitato della coda: ritenta al massimo $max documenti per chiamata, poi
// si ferma (la cassa non aspetta mai). $trasmetti e' un sender iniettabile
// ($fn(xml, cfg): array con 'ok') per i test senza hardware; di default riusa
// il trasporto reale. Righe corrotte scartate, successi spostati in inviati,
// falliti restati in coda. Mai eccezioni, mai payload nei log: solo conteggi.
// Ritorna array('inviati'=>n, 'residui'=>n, 'scarti'=>n).
function fiscale_ritenta_coda(array $cfg, $trasmetti = null, int $max = 10): array
{
    $esito = array('inviati' => 0, 'residui' => 0, 'scarti' => 0);
    $qf = (string)$cfg['queue_file'];
    if (!is_file($qf))
        return $esito;
    $righe = file($qf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!is_array($righe))
    {
        cassa_log('warning', 'fiscale coda lettura fallita');
        return $esito;
    }
    if ($righe === array())
        return $esito;
    if ($max < 1)
        $max = 1;
    $send = is_callable($trasmetti) ? $trasmetti : 'fiscale_trasmetti';
    $resta = array();
    $n = 0;
    foreach ($righe as $line)
    {
        if ($n >= $max)
        {
            $resta[] = $line;
            continue;
        }
        $n++;
        $j = json_decode($line, true);
        if (!is_array($j) || empty($j['xml']) || !is_string($j['xml']))
        {
            $esito['scarti']++;
            continue;
        }
        $t = $send((string)$j['xml'], $cfg);
        if (is_array($t) && !empty($t['ok']))
        {
            $riga = json_encode(array('ts' => date('c'), 'id_ordine' => (int)($j['id_ordine'] ?? 0), 'metodo' => (string)($j['metodo'] ?? \Salsiccia\Cassa\PayMethod::Contanti->value), 'retry' => true));
            if (is_string($riga) && file_put_contents((string)$cfg['sent_file'], $riga . "\n", FILE_APPEND | LOCK_EX) === false)
                cassa_log('warning', 'fiscale inviati scrittura fallita');
            $esito['inviati']++;
        }
        else
            $resta[] = $line;
    }
    if (file_put_contents($qf, $resta === array() ? '' : implode("\n", $resta) . "\n", LOCK_EX) === false)
        cassa_log('error', 'fiscale coda rewrite fallita');
    $esito['residui'] = count($resta);
    return $esito;
}

// Idempotenza minima: un ordine emesso (o gia in coda) non si riemette al click successivo.
function fiscale_gia_emesso(array $cfg, int $id_ordine): bool
{
    foreach (array((string)$cfg['sent_file'], (string)$cfg['queue_file']) as $f)
    {
        if (!is_readable($f))
            continue;
        $lines = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines))
            continue;
        foreach ($lines as $line)
        {
            $j = json_decode($line, true);
            if (is_array($j) && (int)($j['id_ordine'] ?? 0) === $id_ordine)
                return true;
        }
    }
    return false;
}

// Path completo per un ordine: query parametrizzata (mai id interpolato come nel legacy),
// invio o coda, ordine mai bloccato. Ritorna un esito come fiscale_invia().
// T26: $metodo validato via PayMethod enum; ignoto = skipped (rifiutato).
function fiscale_emetti_scontrino(mysqli $db, int $id_ordine, string $metodo = ''): array
{
    $cfg = fiscale_config();
    if ($id_ordine <= 0)
        return array('ok' => false, 'skipped' => true, 'msg' => 'ordine non valido');
    if (\Salsiccia\Cassa\PayMethod::tryFrom($metodo) === null)
        return array('ok' => false, 'skipped' => true, 'msg' => 'metodo non valido');
    if (empty($cfg['mock']) && trim((string)($cfg['ip'] ?? '')) === '')
        return array('ok' => false, 'skipped' => true, 'msg' => 'fiscale non configurato');
    if (fiscale_gia_emesso($cfg, $id_ordine))
        return array('ok' => true, 'duplicato' => true);
    $stmt = $db->prepare("SELECT SUM(righe_ordini.totale) AS totale, prodotti.iva AS iva FROM righe_ordini, prodotti WHERE righe_ordini.id_prodotto = prodotti.id_prodotto AND righe_ordini.id_ordine = ? GROUP BY prodotti.iva");
    if ($stmt === false)
    {
        cassa_log('error', 'fiscale: prepare fallito, coda');
        return fiscale_fallback($cfg, $id_ordine, $metodo, '', 'prepare');
    }
    $stmt->bind_param('i', $id_ordine);
    if (!$stmt->execute())
    {
        cassa_log('error', 'fiscale: query fallita, coda');
        $stmt->close();
        return fiscale_fallback($cfg, $id_ordine, $metodo, '', 'query');
    }
    $res = $stmt->get_result();
    $righe = $res ? $res->fetch_all(MYSQLI_ASSOC) : array();
    $stmt->close();
    $xml = fiscale_build_xml($righe, $metodo);
    if ($xml === '')
        return array('ok' => false, 'skipped' => true, 'msg' => 'niente da fiscalizzare');
    $esito = fiscale_invia($xml, $cfg, $id_ordine, $metodo);
    if (!empty($esito['ok']))
    {
        $riga = json_encode(array('ts' => date('c'), 'id_ordine' => $id_ordine, 'metodo' => $metodo) + (!empty($esito['mock']) ? array('mock' => true) : array()));
        if (is_string($riga) && file_put_contents((string)$cfg['sent_file'], $riga . "\n", FILE_APPEND | LOCK_EX) === false)
            cassa_log('warning', 'fiscale inviati scrittura fallita');
        // Il registratore ha appena risposto: drenare la coda ora costa poco.
        // Se invece avesse fallito non saremmo qui dentro: nessuna attesa
        // aggiuntiva a registratore spento e nessuna latenza in cassa.
        $dren = fiscale_ritenta_coda($cfg);
        if ($dren['inviati'] + $dren['residui'] + $dren['scarti'] > 0)
            cassa_log('info', 'fiscale: coda drenata inviati=' . $dren['inviati'] . ' residui=' . $dren['residui'] . ' scarti=' . $dren['scarti']);
    }
    return $esito;
}
