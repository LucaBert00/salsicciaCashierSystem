<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successore pianificato src/Support/Config.php (§7); rimozione solo dopo la migrazione, rivalutare entro 2026-12-31.
// T24: unica facade log (livello sopra error_log, PSR-3 dopo). Solo codici, mai payload.
if (!function_exists('cassa_log'))
{
    function cassa_log(string $level, string $message): void
    {
        $level = strtolower($level);
        if (!in_array($level, array('debug', 'info', 'warning', 'error'), true))
            $level = 'info';
        error_log('cassa [' . $level . '] ' . $message);
    }
}
// Carica .env (KEY=VALORE) nelle env di processo; le env reali (sistema/Apache) hanno precedenza.
$f = __DIR__ . '/.env';
if (is_readable($f))
{
    $parsed = parse_ini_file($f, false, INI_SCANNER_RAW);
    if ($parsed === false)
        cassa_log('warning', 'env parse fallito');
    foreach ((array)($parsed === false ? array() : $parsed) as $k => $v)
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
        mkdir($d, 0770, true);
    return $d;
}

// Path assoluto in storage/ per nomi spool/coda/log (solo basename, mai traversal).
function salsiccia_storage_path(string $name): string
{
    return salsiccia_storage_dir() . '/' . basename($name);
}

// T28: session hardening (unico punto di avvio sessione).
// Cookie: HttpOnly sempre, SameSite=Lax, Secure solo su HTTPS (la cassa gira
// in HTTP in fiera: Secure=1 la romperebbe). Chiamare prima di session_start
// ovunque al posto di session_start() nudo; kiosk invariato.
function salsiccia_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        return true;
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        return true;
    if (isset($_SERVER['REQUEST_SCHEME']) && strtolower((string)$_SERVER['REQUEST_SCHEME']) === 'https')
        return true;
    return (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
}

function salsiccia_session_start(): bool
{
    if (session_status() === PHP_SESSION_ACTIVE)
        return true;
    $secure = salsiccia_is_https();
    session_set_cookie_params(array('lifetime' => 0, 'path' => '/', 'httponly' => true, 'secure' => $secure, 'samesite' => 'Lax'));
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_secure', $secure ? '1' : '0');
    return session_start();
}

// T28: throttle login server-side (file in storage/, fuori docroot) cosi'
// sopravvive al reset della sessione (il contatore in $_SESSION da solo si
// azzera cancellando il cookie). Scope separati: 'reserved' (backoffice) e
// 'admin' (cassa); stessa regola ovunque: 5 fail -> blocco 60s.
function salsiccia_throttle_file(string $scope, string $ip = ''): string
{
    if ($ip === '')
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'cli');
    if (!preg_match('/^[A-Za-z0-9_-]{1,32}$/', $scope))
        $scope = 'login';
    return salsiccia_storage_path('login_throttle_' . $scope . '_' . sha1($ip) . '.json');
}

function salsiccia_throttle_leggi(string $file): array
{
    if (!is_readable($file))
        return array('fail' => 0, 'block_until' => 0);
    $j = json_decode((string)file_get_contents($file), true);
    if (!is_array($j))
        return array('fail' => 0, 'block_until' => 0);
    return array('fail' => max(0, (int)($j['fail'] ?? 0)), 'block_until' => max(0, (int)($j['block_until'] ?? 0)));
}

function salsiccia_login_throttled(string $scope, string $ip = '', string $file = ''): bool
{
    $f = $file !== '' ? $file : salsiccia_throttle_file($scope, $ip);
    $st = salsiccia_throttle_leggi($f);
    if ($st['block_until'] > 0 && $st['block_until'] <= time())
    {
        @unlink($f); // blocco scaduto: si riparte puliti come il ramo sessione
        return false;
    }
    return $st['block_until'] > time();
}

function salsiccia_login_fail(string $scope, string $ip = '', string $file = ''): void
{
    $f = $file !== '' ? $file : salsiccia_throttle_file($scope, $ip);
    $st = salsiccia_throttle_leggi($f);
    if ($st['block_until'] > time())
        return; // sotto blocco: niente estensioni, come il ramo sessione
    $st['fail']++;
    if ($st['fail'] >= 5)
    {
        $st['fail'] = 0;
        $st['block_until'] = time() + 60;
    }
    $json = json_encode($st);
    if (is_string($json))
        @file_put_contents($f, $json, LOCK_EX);
}

function salsiccia_login_ok(string $scope, string $ip = '', string $file = ''): void
{
    $f = $file !== '' ? $file : salsiccia_throttle_file($scope, $ip);
    @unlink($f);
}

// Gate stampa etichette (F4.1 #51): unico punto di definizione della
// tabella di verita' stampante/connessione/linguaggio. Usato sia dal gate
// stampa_permessa in PrintService::generaFileStampa() (src/Cassa/PrintService.php) sia dal calcolo
// di CONTINUOUS_LABEL (set.inc): stessa tabella, nessun duplicato.
function printer_gate_allowed($name, $conn, $lang)
{
    if ($name === 'ZD230')
        return ($conn === 'DIRETTA' || $conn === 'RETE') && $lang === 'ZPL';
    // GX420t: solo DIRETTA, unica dual ZPL+EPL.
    if ($name === 'GX420t')
        return $conn === 'DIRETTA' && ($lang === 'ZPL' || $lang === 'EPL');
    if ($name === 'TLP2844' || $name === 'LP2844')
        return $conn === 'DIRETTA' && $lang === 'EPL';
    return false;
}

// Carta continua (scontrino unico) vs biglietti singoli: GX420t
// (DIRETTA dual) e ZD230 (RETE/DIRETTA ZPL) fanno
// scontrino unico su richiesta deploy; TLP2844/LP2844 fanno singoli.
// Deploy attuale: ZD230 continua.
function printer_is_continuous($name, $conn, $lang)
{
    return printer_gate_allowed($name, $conn, $lang) && ($name === 'ZD230' || $name === 'GX420t');
}

// F4.1 #51: taglierino, solo la GX420t lo monta. Definita qui ma NON usata
// (l'append di taglio e' F1, fuori scope).
if (!function_exists('printer_has_cutter'))
{
    function printer_has_cutter($name)
    {
        return $name === 'GX420t';
    }
}

if (!function_exists('printer_taglia_permesso'))
{
    function printer_taglia_permesso($name, $conn, $lang)
    {
        return printer_has_cutter($name) && printer_is_continuous($name, $conn, $lang);
    }
}

// F4.1 #51: registry stampanti note, nomi memorizzati anche se mai collegati.
// Solo la GX420t e' dual EPL+ZPL; le altre solo la loro modalita' (stesso gate sopra).
if (!function_exists('printer_known_printers'))
{
    function printer_known_printers()
    {
        return array(
            array('name' => 'ZD230', 'connection' => 'RETE', 'language' => 'ZPL'),
            array('name' => 'GX420t', 'connection' => 'DIRETTA', 'language' => 'ZPL'),
            array('name' => 'TLP2844', 'connection' => 'DIRETTA', 'language' => 'EPL'),
            array('name' => 'LP2844', 'connection' => 'DIRETTA', 'language' => 'EPL'),
        );
    }
}

// F4.1 #51: selezione persistita in storage/, mai rewrite di PHP source.
// File JSON scritto dalla UI (F4.5), letto a boot. Combo non a gate => null.
if (!function_exists('printer_selection_file'))
{
    function printer_selection_file()
    {
        return salsiccia_storage_path('stampante_selezione.json');
    }
}

if (!function_exists('printer_leggi_selezione'))
{
    function printer_leggi_selezione()
    {
        $f = printer_selection_file();
        $raw = is_readable($f) ? @file_get_contents($f) : false;
        if ($raw === false)
            return null;
        $d = json_decode($raw, true);
        if (!is_array($d) || empty($d['name']) || empty($d['connection']) || empty($d['language']))
            return null;
        $name = (string)$d['name'];
        $conn = (string)$d['connection'];
        $lang = (string)$d['language'];
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name))
            return null;
        if (!printer_gate_allowed($name, $conn, $lang))
            return null;
        $ip = isset($d['ip']) ? trim((string)$d['ip']) : '';
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) === false)
            $ip = '';
        return array('name' => $name, 'connection' => $conn, 'language' => $lang, 'ip' => $ip);
    }
}

// F4.1 #51: raggiungibilita' minima per il gate di selezione (F4.5).
// DIRETTA = coda locale, sempre raggiungibile qui (stato CUPS veritiero e' F4.2);
// RETE = socket TCP corto verso l'IP, ping fallito gestito senza fatal (anche su Windows senza CUPS).
if (!function_exists('printer_ping'))
{
    function printer_ping($ip, $port = 21, $timeout = 2)
    {
        $ip = trim((string)$ip);
        if (filter_var($ip, FILTER_VALIDATE_IP) === false)
            return false;
        $fp = @fsockopen($ip, (int)$port, $errno, $errstr, (int)$timeout);
        if ($fp === false)
            return false;
        fclose($fp);
        return true;
    }
}

if (!function_exists('printer_is_reachable'))
{
    function printer_is_reachable($name, $conn, $ip = '')
    {
        if ($conn !== 'RETE')
            return true;
        return printer_ping($ip);
    }
}

// F4.2 #52: stato CUPS veritiero, solo helper (nessun uso in gate/UI:
// quello e' F4.3/F4.5, fuori scope). DIRETTA non verificabile senza
// lpstat => rosso "non verificabile"; RETE resta ping-only sopra.
// Mai fatal senza CUPS: shell/exec con fallback come upstream.
if (!function_exists('printer_cups_queue'))
{
    // Nome coda CUPS reale: oggi identita' del nome logico, nessuna mappatura.
    function printer_cups_queue($name)
    {
        return (string)$name;
    }
}

if (!function_exists('printer_lpstat_bin'))
{
    function printer_lpstat_bin()
    {
        $lpstat = (string)strtok(trim((string)@shell_exec('command -v lpstat 2>/dev/null')), "\r\n");
        if ($lpstat === '' || !is_executable($lpstat))
            return '';
        return $lpstat;
    }
}

if (!function_exists('printer_lpstat_parse_default'))
{
    // Default CUPS da righe `lpstat -t` ("system default destination: <nome>").
    function printer_lpstat_parse_default($righe)
    {
        foreach ((array)$righe as $r)
        {
            if (preg_match('/^system default destination:\s*(\S.*?)\s*$/', (string)$r, $m))
                return $m[1];
        }
        return '';
    }
}

if (!function_exists('printer_lpstat_snapshot'))
{
    // Snapshot `lpstat -t` (equivale a -r -d -c -v -a -p -o): riusato da tutte
    // le code a request, cosi' il controllo costa una sola exec a request.
    function printer_lpstat_snapshot()
    {
        static $mem = null;
        if (is_array($mem))
            return $mem;
        $mem = array('verificabile' => false, 'righe' => array(), 'predefinita' => '');
        $lpstat = printer_lpstat_bin();
        if ($lpstat === '')
            return $mem; // atteso senza CUPS: nessun log, solo rosso "non verificabile"
        $righe = array();
        $rc = 1;
        @exec(escapeshellarg($lpstat) . ' -t 2>/dev/null', $righe, $rc);
        if ($rc !== 0)
        {
            cassa_log('error', 'lpstat -t fallito');
            return $mem;
        }
        $mem['verificabile'] = true;
        $mem['righe'] = $righe;
        $mem['predefinita'] = printer_lpstat_parse_default($righe);
        return $mem;
    }
}

if (!function_exists('printer_lpstat_parse_stato'))
{
    // Parser puro sui formati CUPS (man lpstat OpenPrinting): -p
    // "printer <nome> is idle. enabled since ...", "now printing ...",
    // "disabled since ... - <motivo>", "printer <nome> unknown";
    // -a "<nome> (not )accepting requests since ...";
    // -v "device for <nome>: <device>"; -o righe "<nome>-<id> ...".
    function printer_lpstat_parse_stato($name, $righe, $predefinita)
    {
        $name = (string)$name;
        $st = array('enabled' => false, 'state' => 'unknown', 'accepting' => false, 'device' => '', 'since' => '', 'reason' => '', 'jobs' => 0, 'predefinita' => false);
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name) || empty($righe))
            return $st;
        $q = preg_quote($name, '/');
        foreach ((array)$righe as $r)
        {
            $r = (string)$r;
            if (preg_match('/^printer\s+' . $q . '\s+(.*)$/', $r, $m))
            {
                $d = (string)$m[1];
                $low = strtolower($d);
                if (strpos($low, 'unknown') !== false)
                {
                    $st['state'] = 'unknown';
                    $st['enabled'] = false;
                }
                elseif (strpos($low, 'disabled') !== false)
                {
                    $st['state'] = 'disabled';
                    $st['enabled'] = false;
                    if (preg_match('/disabled since\s+(.*?)\s+-\s+(.+?)\s*$/i', $d, $mm))
                    {
                        $st['since'] = trim((string)$mm[1]);
                        $st['reason'] = trim((string)$mm[2]);
                    }
                    elseif (preg_match('/disabled since\s*(.*?)\s*$/i', $d, $mm))
                    {
                        $st['since'] = trim((string)$mm[1]);
                    }
                    if ($st['reason'] === '')
                        $st['reason'] = 'disabilitata';
                }
                elseif (strpos($low, 'now printing') !== false)
                {
                    $st['state'] = 'printing';
                    $st['enabled'] = (strpos($low, 'enabled') !== false && strpos($low, 'disabled') === false);
                    if (preg_match('/enabled since\s*(.*?)\s*$/i', $d, $mm))
                        $st['since'] = trim((string)$mm[1]);
                }
                elseif (strpos($low, 'is idle') !== false)
                {
                    $st['state'] = 'idle';
                    $st['enabled'] = (strpos($low, 'enabled') !== false && strpos($low, 'disabled') === false);
                    if (preg_match('/enabled since\s*(.*?)\s*$/i', $d, $mm))
                        $st['since'] = trim((string)$mm[1]);
                }
            }
            elseif (preg_match('/^' . $q . '\s+(not\s+)?accepting requests(\s+since\s*(.*?))?\s*$/i', $r, $m))
            {
                $st['accepting'] = (trim(strtolower((string)$m[1])) === '');
                if ($st['since'] === '' && isset($m[3]))
                    $st['since'] = trim((string)$m[3]);
            }
            elseif (preg_match('/^device for\s+' . $q . ':\s*(.*?)\s*$/i', $r, $m))
            {
                $st['device'] = trim((string)$m[1]);
            }
            elseif (preg_match('/^' . $q . '-\d+\s/', $r))
            {
                $st['jobs']++;
            }
        }
        $st['predefinita'] = ((string)$predefinita !== '' && (string)$predefinita === $name);
        if ($st['state'] === 'unknown' && $st['reason'] === '')
            $st['reason'] = 'non configurata sul kiosk';
        if (!$st['accepting'] && ($st['state'] === 'idle' || $st['state'] === 'printing') && $st['reason'] === '')
            $st['reason'] = 'coda non accetta richieste';
        return $st;
    }
}

if (!function_exists('printer_lpstat_p_snapshot'))
{
    // Conferma stato via `lpstat -p`: lo snapshot `lpstat -t` resta la base
    // (coda+device+accepting), la riga `-p` conferma idle/printing per coda.
    // Una sola exec a request senza argomenti, parse per nome in PHP come sopra:
    // nessun nome in shell, la whitelist resta obbligatoria al parse. Se `-p`
    // non e' disponibile la conferma e' neutra (vale la base); se `-p` dice
    // disabled/unknown (o la coda manca) vince il rosso anche con `-t` idle.
    function printer_lpstat_p_snapshot()
    {
        static $mem = null;
        if (is_array($mem))
            return $mem;
        $mem = array('verificabile' => false, 'righe' => array());
        $lpstat = printer_lpstat_bin();
        if ($lpstat === '')
            return $mem;
        $righe = array();
        $rc = 1;
        @exec(escapeshellarg($lpstat) . ' -p 2>/dev/null', $righe, $rc);
        if ($rc !== 0)
        {
            cassa_log('error', 'lpstat -p fallito');
            return $mem;
        }
        $mem['verificabile'] = true;
        $mem['righe'] = $righe;
        return $mem;
    }
}

if (!function_exists('printer_lpstat_p_stato'))
{
    // Stato `-p` di una coda reale: null se non verificabile (conferma neutra),
    // altrimenti parser sopra sulle sole righe `-p` (coda assente => unknown).
    function printer_lpstat_p_stato($queue)
    {
        $queue = (string)$queue;
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $queue))
            return null;
        $snap = printer_lpstat_p_snapshot();
        if (empty($snap['verificabile']))
            return null;
        return printer_lpstat_parse_stato($queue, $snap['righe'], '');
    }
}

if (!function_exists('printer_lpstat_applica_conferma_p'))
{
    // Fusione pura base `-t` + conferma `-p` (testabile con fixture senza exec):
    // conferma idle/printing o neutra => base invariata; qualsiasi altro stato
    // `-p` (disabled, unknown, coda assente) => rosso, device/accepting/jobs
    // della base restano per il motivo.
    function printer_lpstat_applica_conferma_p($st, $conf)
    {
        $st = (array)$st;
        if (!is_array($conf) || empty($conf))
            return $st;
        $pcs = isset($conf['state']) ? (string)$conf['state'] : 'unknown';
        if ($pcs === 'idle' || $pcs === 'printing')
            return $st;
        $st['state'] = $pcs;
        $st['enabled'] = false;
        $pr = isset($conf['reason']) ? trim((string)$conf['reason']) : '';
        if ($pr !== '')
            $st['reason'] = $pr;
        elseif (trim((string)$st['reason']) === '')
            $st['reason'] = 'non confermata via lpstat -p';
        $ps = isset($conf['since']) ? trim((string)$conf['since']) : '';
        if (trim((string)$st['since']) === '' && $ps !== '')
            $st['since'] = $ps;
        return $st;
    }
}

if (!function_exists('printer_lpstat_stato'))
{
    // Stato veritiero di una coda locale: snapshot condiviso + parser sopra.
    // Nome logico tradotto in coda reale via printer_cups_queue().
    // Ritorna enabled, state (idle|printing|disabled|unknown), accepting, device,
    // since, reason, jobs, predefinita + verificabile (false senza lpstat).
    function printer_lpstat_stato($name)
    {
        $name = (string)$name;
        $no = array('verificabile' => false, 'enabled' => false, 'state' => 'unknown', 'accepting' => false, 'device' => '', 'since' => '', 'reason' => 'non verificabile sul kiosk', 'jobs' => 0, 'predefinita' => false);
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name))
            return $no;
        $snap = printer_lpstat_snapshot();
        if (empty($snap['verificabile']))
            return $no;
        $st = printer_lpstat_parse_stato(printer_cups_queue($name), $snap['righe'], $snap['predefinita']);
        $st['verificabile'] = true;
        $st = printer_lpstat_applica_conferma_p($st, printer_lpstat_p_stato(printer_cups_queue($name)));
        if ($st['state'] === 'unknown' && (string)$st['reason'] === '')
            $st['reason'] = 'non configurata sul kiosk';
        return $st;
    }
}

if (!function_exists('printer_stato_locale_ok'))
{
    // Giudizio puro su uno stato gia parsato (+ presenza USB gia rilevata):
    // testabile con fixture senza exec. $usbPresente=true di default per non-USB.
    // printing vale come ok alla pari di idle; USB scollegata/spenta/ad altro
    // host => false (rosso) anche a coda idle.
    function printer_stato_locale_ok($st, $usbPresente = true)
    {
        $st = (array)$st;
        if (empty($st['verificabile']) || empty($st['enabled']) || empty($st['accepting']))
            return false;
        if ($st['state'] !== 'idle' && $st['state'] !== 'printing')
            return false;
        $dev = isset($st['device']) ? trim((string)$st['device']) : '';
        if ($dev === '' || $dev === '///dev/null' || $dev === '/dev/null' || stripos($dev, 'file:///dev/null') === 0)
            return false;
        if (stripos($dev, 'usb://') === 0 && !$usbPresente)
            return false;
        return true;
    }
}

if (!function_exists('printer_usb_presente_da_evidenza'))
{
    // Presenza USB pura da evidenza raccolta (righe `lpinfo -v`): testabile con
    // fixture. Nodi /dev scartati (non attendibili); il primo parametro resta
    // solo per compatibilita' fixture e resta sempre vuoto in uso reale.
    // Con seriale nel device atteso serve il match sul seriale (piu code USB
    // sulla stessa cassa), altrimenti basta un usb://.
    function printer_usb_presente_da_evidenza($nodiUsb, $righeLpinfo, $deviceAtteso)
    {
        if (!empty($nodiUsb))
            return true;
        $serial = '';
        if (preg_match('/serial=([^?&\s]+)/i', (string)$deviceAtteso, $m))
            $serial = trim((string)$m[1]);
        foreach ((array)$righeLpinfo as $r)
        {
            $r = (string)$r;
            if (stripos($r, 'usb://') === false)
                continue;
            if ($serial !== '')
            {
                if (stripos($r, $serial) !== false)
                    return true;
            }
            else
                return true;
        }
        return false;
    }
}

if (!function_exists('printer_usb_locale_presente'))
{
    // Presenza USB reale su questa cassa: solo `lpinfo -v` che elenca il device
    // (discovery CUPS via libusb). I nodi /dev/usb/lp*|/dev/usblp* non si usano:
    // non attendibili (modulo usblp spesso blacklistato, permessi www-data).
    // Cache per device a request. Mai input utente in shell.
    // Se `lpinfo` e' fallito/mancante NON si conclude "assente": ci si fida della
    // coda CUPS (lpr stampa anche quando il web non vede il device).
    // Solo lpinfo riuscito senza il seriale atteso = assenza vera.
    function printer_usb_locale_presente($deviceAtteso = '')
    {
        static $mem = array();
        $deviceAtteso = (string)$deviceAtteso;
        if (isset($mem[$deviceAtteso]))
            return $mem[$deviceAtteso];
        $righe = array();
        $lpinfoOk = false;
        $lpinfo = (string)strtok(trim((string)@shell_exec('command -v lpinfo 2>/dev/null')), "\r\n");
        if ($lpinfo !== '' && is_executable($lpinfo))
        {
            $out = array();
            $rc = 1;
            @exec(escapeshellarg($lpinfo) . ' -v 2>/dev/null', $out, $rc);
            if ($rc === 0)
            {
                $righe = $out;
                $lpinfoOk = true;
            }
        }
        if (!$lpinfoOk)
        {
            // detection cieca, non prova di assenza; la coda CUPS resta la verita'.
            $mem[$deviceAtteso] = true;
            return $mem[$deviceAtteso];
        }
        $mem[$deviceAtteso] = printer_usb_presente_da_evidenza(array(), $righe, $deviceAtteso);
        return $mem[$deviceAtteso];
    }
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
    $raw = is_readable(cassa_flags_file()) ? file_get_contents(cassa_flags_file()) : false;
    if (is_string($raw) && $raw !== '')
    {
        $j = json_decode($raw, true);
        if (is_array($j) && isset($j['modalita_fiera']))
            return $j['modalita_fiera'] === '1' || $j['modalita_fiera'] === 1 || $j['modalita_fiera'] === true;
    }
    $cfg = __DIR__ . '/config/cassa.php';
    if (is_readable($cfg))
    {
        $arr = is_readable($cfg) ? include $cfg : false;
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
    if (file_put_contents($tmp, $json, LOCK_EX) === false)
    {
        cassa_log('error', 'cassa_flags scrittura fallita');
        return;
    }
    if (!rename($tmp, $f))
        cassa_log('error', 'cassa_flags rename fallita');
}
?>
