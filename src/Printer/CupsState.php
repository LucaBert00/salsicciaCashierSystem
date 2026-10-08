<?php

declare(strict_types=1);

namespace Salsiccia\Printer;

use Salsiccia\Support\Env;

// Probing CUPS/USB della cassa (F5.3 #105, docs/ARCHITETTURA_REVISTA.md
// §10 punto 18 + §6 352-714 + §9 target + §7 integrazione hardware/OS
// → classe con metodi statici, es. CupsState::stato()).
// Corpi verbatim da env.inc:352-714: printer_ping, printer_is_reachable,
// printer_cups_queue, printer_lpstat_bin, printer_lpstat_parse_default,
// printer_lpstat_snapshot, printer_lpstat_parse_stato,
// printer_lpstat_p_snapshot, printer_lpstat_p_stato,
// printer_lpstat_applica_conferma_p, printer_lpstat_stato,
// printer_stato_locale_ok, printer_usb_presente_da_evidenza,
// printer_usb_locale_presente. Stessi comandi shell (command -v lpstat/lpinfo,
// lpstat -t/-p, lpinfo -v), stessi regex di parsing, stessi codici di uscita,
// stessi ritorni e stesse cache per request.
// Metodi statici: nessun stato, autoload PSR-4 senza toccare composer.json.
// Transitorio onesto: env.inc resta dov'e con le funzioni originali ancora
// presenti (delete solo in F5.5 #107); nessun consumer migrato qui
// (PrinterConfig::raggiungibile, Power::schedula, PrintService, AdminView
// switchPrinter, PrinterF4GateParserStatoTest invariati). Unica diff logica
// oltre a namespace/firma: log via Env::log (stessa facade cassa_log, F5.1).
final class CupsState
{
    // F4.1 #51: raggiungibilita' minima per il gate di selezione (F4.5).
    // DIRETTA = coda locale, sempre raggiungibile qui (stato CUPS veritiero e' F4.2);
    // RETE = socket TCP corto verso l'IP, ping fallito gestito senza fatal (anche su Windows senza CUPS).
    /**
     * @param mixed $ip
     * @param mixed $port
     * @param mixed $timeout
     */
    public static function ping($ip, $port = 21, $timeout = 2): bool
    {
        $ip = trim((string)$ip);
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }
        $fp = @fsockopen($ip, (int)$port, $errno, $errstr, (int)$timeout);
        if ($fp === false) {
            return false;
        }
        fclose($fp);
        return true;
    }

    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $ip
     */
    public static function isReachable($name, $conn, $ip = ''): bool
    {
        if ($conn !== 'RETE') {
            return true;
        }
        return self::ping($ip);
    }

    // F4.2 #52: stato CUPS veritiero, solo helper (nessun uso in gate/UI:
    // quello e' F4.3/F4.5, fuori scope). DIRETTA non verificabile senza
    // lpstat => rosso "non verificabile"; RETE resta ping-only sopra.
    // Mai fatal senza CUPS: shell/exec con fallback come upstream.
    // Nome coda CUPS reale: oggi identita' del nome logico, nessuna mappatura.
    /**
     * @param mixed $name
     */
    public static function cupsQueue($name): string
    {
        return (string)$name;
    }

    public static function lpstatBin(): string
    {
        $lpstat = (string)strtok(trim((string)@shell_exec('command -v lpstat 2>/dev/null')), "\r\n");
        if ($lpstat === '' || !is_executable($lpstat)) {
            return '';
        }
        return $lpstat;
    }

    // Default CUPS da righe `lpstat -t` ("system default destination: <nome>").
    /**
     * @param mixed $righe
     */
    public static function lpstatParseDefault($righe): string
    {
        foreach ((array)$righe as $r) {
            if (preg_match('/^system default destination:\s*(\S.*?)\s*$/', (string)$r, $m)) {
                return $m[1];
            }
        }
        return '';
    }

    // Snapshot `lpstat -t` (equivale a -r -d -c -v -a -p -o): riusato da tutte
    // le code a request, cosi' il controllo costa una sola exec a request.
    public static function lpstatSnapshot(): array
    {
        static $mem = null;
        if (is_array($mem)) {
            return $mem;
        }
        $mem = array('verificabile' => false, 'righe' => array(), 'predefinita' => '');
        $lpstat = self::lpstatBin();
        if ($lpstat === '') {
            return $mem; // atteso senza CUPS: nessun log, solo rosso "non verificabile"
        }
        $righe = array();
        $rc = 1;
        @exec(escapeshellarg($lpstat) . ' -t 2>/dev/null', $righe, $rc);
        if ($rc !== 0) {
            Env::log('error', 'lpstat -t fallito');
            return $mem;
        }
        $mem['verificabile'] = true;
        $mem['righe'] = $righe;
        $mem['predefinita'] = self::lpstatParseDefault($righe);
        return $mem;
    }

    // Parser puro sui formati CUPS (man lpstat OpenPrinting): -p
    // "printer <nome> is idle. enabled since ...", "now printing ...",
    // "disabled since ... - <motivo>", "printer <nome> unknown";
    // -a "<nome> (not )accepting requests since ...";
    // -v "device for <nome>: <device>"; -o righe "<nome>-<id> ...".
    /**
     * @param mixed $name
     * @param mixed $righe
     * @param mixed $predefinita
     */
    public static function lpstatParseStato($name, $righe, $predefinita): array
    {
        $name = (string)$name;
        $st = array('enabled' => false, 'state' => 'unknown', 'accepting' => false, 'device' => '', 'since' => '', 'reason' => '', 'jobs' => 0, 'predefinita' => false);
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name) || empty($righe)) {
            return $st;
        }
        $q = preg_quote($name, '/');
        foreach ((array)$righe as $r) {
            $r = (string)$r;
            if (preg_match('/^printer\s+' . $q . '\s+(.*)$/', $r, $m)) {
                $d = (string)$m[1];
                $low = strtolower($d);
                if (strpos($low, 'unknown') !== false) {
                    $st['state'] = 'unknown';
                    $st['enabled'] = false;
                } elseif (strpos($low, 'disabled') !== false) {
                    $st['state'] = 'disabled';
                    $st['enabled'] = false;
                    if (preg_match('/disabled since\s+(.*?)\s+-\s+(.+?)\s*$/i', $d, $mm)) {
                        $st['since'] = trim((string)$mm[1]);
                        $st['reason'] = trim((string)$mm[2]);
                    } elseif (preg_match('/disabled since\s*(.*?)\s*$/i', $d, $mm)) {
                        $st['since'] = trim((string)$mm[1]);
                    }
                    if ($st['reason'] === '') {
                        $st['reason'] = 'disabilitata';
                    }
                } elseif (strpos($low, 'now printing') !== false) {
                    $st['state'] = 'printing';
                    $st['enabled'] = (strpos($low, 'enabled') !== false && strpos($low, 'disabled') === false);
                    if (preg_match('/enabled since\s*(.*?)\s*$/i', $d, $mm)) {
                        $st['since'] = trim((string)$mm[1]);
                    }
                } elseif (strpos($low, 'is idle') !== false) {
                    $st['state'] = 'idle';
                    $st['enabled'] = (strpos($low, 'enabled') !== false && strpos($low, 'disabled') === false);
                    if (preg_match('/enabled since\s*(.*?)\s*$/i', $d, $mm)) {
                        $st['since'] = trim((string)$mm[1]);
                    }
                }
            } elseif (preg_match('/^' . $q . '\s+(not\s+)?accepting requests(\s+since\s*(.*?))?\s*$/i', $r, $m)) {
                $st['accepting'] = (trim(strtolower((string)$m[1])) === '');
                if ($st['since'] === '' && isset($m[3])) {
                    $st['since'] = trim((string)$m[3]);
                }
            } elseif (preg_match('/^device for\s+' . $q . ':\s*(.*?)\s*$/i', $r, $m)) {
                $st['device'] = trim((string)$m[1]);
            } elseif (preg_match('/^' . $q . '-\d+\s/', $r)) {
                $st['jobs']++;
            }
        }
        $st['predefinita'] = ((string)$predefinita !== '' && (string)$predefinita === $name);
        if ($st['state'] === 'unknown' && $st['reason'] === '') {
            $st['reason'] = 'non configurata sul kiosk';
        }
        if (!$st['accepting'] && ($st['state'] === 'idle' || $st['state'] === 'printing') && $st['reason'] === '') {
            $st['reason'] = 'coda non accetta richieste';
        }
        return $st;
    }

    // Conferma stato via `lpstat -p`: lo snapshot `lpstat -t` resta la base
    // (coda+device+accepting), la riga `-p` conferma idle/printing per coda.
    // Una sola exec a request senza argomenti, parse per nome in PHP come sopra:
    // nessun nome in shell, la whitelist resta obbligatoria al parse. Se `-p`
    // non e' disponibile la conferma e' neutra (vale la base); se `-p` dice
    // disabled/unknown (o la coda manca) vince il rosso anche con `-t` idle.
    public static function lpstatPSnapshot(): array
    {
        static $mem = null;
        if (is_array($mem)) {
            return $mem;
        }
        $mem = array('verificabile' => false, 'righe' => array());
        $lpstat = self::lpstatBin();
        if ($lpstat === '') {
            return $mem;
        }
        $righe = array();
        $rc = 1;
        @exec(escapeshellarg($lpstat) . ' -p 2>/dev/null', $righe, $rc);
        if ($rc !== 0) {
            Env::log('error', 'lpstat -p fallito');
            return $mem;
        }
        $mem['verificabile'] = true;
        $mem['righe'] = $righe;
        return $mem;
    }

    // Stato `-p` di una coda reale: null se non verificabile (conferma neutra),
    // altrimenti parser sopra sulle sole righe `-p` (coda assente => unknown).
    /**
     * @param mixed $queue
     */
    public static function lpstatPStato($queue): ?array
    {
        $queue = (string)$queue;
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $queue)) {
            return null;
        }
        $snap = self::lpstatPSnapshot();
        if (empty($snap['verificabile'])) {
            return null;
        }
        return self::lpstatParseStato($queue, $snap['righe'], '');
    }

    // Fusione pura base `-t` + conferma `-p` (testabile con fixture senza exec):
    // conferma idle/printing o neutra => base invariata; qualsiasi altro stato
    // `-p` (disabled, unknown, coda assente) => rosso, device/accepting/jobs
    // della base restano per il motivo.
    /**
     * @param mixed $st
     * @param mixed $conf
     */
    public static function lpstatApplicaConfermaP($st, $conf): array
    {
        $st = (array)$st;
        if (!is_array($conf) || empty($conf)) {
            return $st;
        }
        $pcs = isset($conf['state']) ? (string)$conf['state'] : 'unknown';
        if ($pcs === 'idle' || $pcs === 'printing') {
            return $st;
        }
        $st['state'] = $pcs;
        $st['enabled'] = false;
        $pr = isset($conf['reason']) ? trim((string)$conf['reason']) : '';
        if ($pr !== '') {
            $st['reason'] = $pr;
        } elseif (trim((string)$st['reason']) === '') {
            $st['reason'] = 'non confermata via lpstat -p';
        }
        $ps = isset($conf['since']) ? trim((string)$conf['since']) : '';
        if (trim((string)$st['since']) === '' && $ps !== '') {
            $st['since'] = $ps;
        }
        return $st;
    }

    // Stato veritiero di una coda locale: snapshot condiviso + parser sopra.
    // Nome logico tradotto in coda reale via cupsQueue().
    // Ritorna enabled, state (idle|printing|disabled|unknown), accepting, device,
    // since, reason, jobs, predefinita + verificabile (false senza lpstat).
    /**
     * @param mixed $name
     */
    public static function stato($name): array
    {
        $name = (string)$name;
        $no = array('verificabile' => false, 'enabled' => false, 'state' => 'unknown', 'accepting' => false, 'device' => '', 'since' => '', 'reason' => 'non verificabile sul kiosk', 'jobs' => 0, 'predefinita' => false);
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            return $no;
        }
        $snap = self::lpstatSnapshot();
        if (empty($snap['verificabile'])) {
            return $no;
        }
        $st = self::lpstatParseStato(self::cupsQueue($name), $snap['righe'], $snap['predefinita']);
        $st['verificabile'] = true;
        $st = self::lpstatApplicaConfermaP($st, self::lpstatPStato(self::cupsQueue($name)));
        if ($st['state'] === 'unknown' && (string)$st['reason'] === '') {
            $st['reason'] = 'non configurata sul kiosk';
        }
        return $st;
    }

    // Giudizio puro su uno stato gia parsato (+ presenza USB gia rilevata):
    // testabile con fixture senza exec. $usbPresente=true di default per non-USB.
    // printing vale come ok alla pari di idle; USB scollegata/spenta/ad altro
    // host => false (rosso) anche a coda idle.
    /**
     * @param mixed $st
     * @param mixed $usbPresente
     */
    public static function statoLocaleOk($st, $usbPresente = true): bool
    {
        $st = (array)$st;
        if (empty($st['verificabile']) || empty($st['enabled']) || empty($st['accepting'])) {
            return false;
        }
        if ($st['state'] !== 'idle' && $st['state'] !== 'printing') {
            return false;
        }
        $dev = isset($st['device']) ? trim((string)$st['device']) : '';
        if ($dev === '' || $dev === '///dev/null' || $dev === '/dev/null' || stripos($dev, 'file:///dev/null') === 0) {
            return false;
        }
        if (stripos($dev, 'usb://') === 0 && !$usbPresente) {
            return false;
        }
        return true;
    }

    // Presenza USB pura da evidenza raccolta (righe `lpinfo -v`): testabile con
    // fixture. Nodi /dev scartati (non attendibili); il primo parametro resta
    // solo per compatibilita' fixture e resta sempre vuoto in uso reale.
    // Con seriale nel device atteso serve il match sul seriale (piu code USB
    // sulla stessa cassa), altrimenti basta un usb://.
    /**
     * @param mixed $nodiUsb
     * @param mixed $righeLpinfo
     * @param mixed $deviceAtteso
     */
    public static function usbPresenteDaEvidenza($nodiUsb, $righeLpinfo, $deviceAtteso): bool
    {
        if (!empty($nodiUsb)) {
            return true;
        }
        $serial = '';
        if (preg_match('/serial=([^?&\s]+)/i', (string)$deviceAtteso, $m)) {
            $serial = trim((string)$m[1]);
        }
        foreach ((array)$righeLpinfo as $r) {
            $r = (string)$r;
            if (stripos($r, 'usb://') === false) {
                continue;
            }
            if ($serial !== '') {
                if (stripos($r, $serial) !== false) {
                    return true;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    // Presenza USB reale su questa cassa: solo `lpinfo -v` che elenca il device
    // (discovery CUPS via libusb). I nodi /dev/usb/lp*|/dev/usblp* non si usano:
    // non attendibili (modulo usblp spesso blacklistato, permessi www-data).
    // Cache per device a request. Mai input utente in shell.
    // Se `lpinfo` e' fallito/mancante NON si conclude "assente": ci si fida della
    // coda CUPS (lpr stampa anche quando il web non vede il device).
    // Solo lpinfo riuscito senza il seriale atteso = assenza vera.
    /**
     * @param mixed $deviceAtteso
     */
    public static function usbLocalePresente($deviceAtteso = ''): bool
    {
        static $mem = array();
        $deviceAtteso = (string)$deviceAtteso;
        if (isset($mem[$deviceAtteso])) {
            return $mem[$deviceAtteso];
        }
        $righe = array();
        $lpinfoOk = false;
        $lpinfo = (string)strtok(trim((string)@shell_exec('command -v lpinfo 2>/dev/null')), "\r\n");
        if ($lpinfo !== '' && is_executable($lpinfo)) {
            $out = array();
            $rc = 1;
            @exec(escapeshellarg($lpinfo) . ' -v 2>/dev/null', $out, $rc);
            if ($rc === 0) {
                $righe = $out;
                $lpinfoOk = true;
            }
        }
        if (!$lpinfoOk) {
            // detection cieca, non prova di assenza; la coda CUPS resta la verita'.
            $mem[$deviceAtteso] = true;
            return $mem[$deviceAtteso];
        }
        $mem[$deviceAtteso] = self::usbPresenteDaEvidenza(array(), $righe, $deviceAtteso);
        return $mem[$deviceAtteso];
    }
}
