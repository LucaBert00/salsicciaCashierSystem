<?php
declare(strict_types=1);
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
