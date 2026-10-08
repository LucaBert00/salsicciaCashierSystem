<?php

declare(strict_types=1);

namespace Salsiccia\Support;

// Hardening sessione, unico punto di avvio (F5.1 #103,
// docs/ARCHITETTURA_REVISTA.md §10 punto 19 + §6 52-75 + §9 target).
// Corpi verbatim da env.inc:52-75 (stessi flag cookie: HttpOnly sempre,
// SameSite=Lax, Secure solo su HTTPS perche la cassa gira in HTTP in fiera).
// Metodi statici: nessun stato, autoload PSR-4 senza toccare composer.json.
// Transitorio onesto: env.inc resta dov'e con le funzioni originali ancora
// presenti (delete solo in F5.5); nessun consumer migrato qui
// (public/reserved/*.php, helpers.php csrf invariati). Chiamare start() prima
// di session_start() ovunque al posto di session_start() nudo; kiosk invariato.
final class Session
{
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }
        if (isset($_SERVER['REQUEST_SCHEME']) && strtolower((string)$_SERVER['REQUEST_SCHEME']) === 'https') {
            return true;
        }
        return (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
    }

    public static function start(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }
        $secure = self::isHttps();
        session_set_cookie_params(array('lifetime' => 0, 'path' => '/', 'httponly' => true, 'secure' => $secure, 'samesite' => 'Lax'));
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_secure', $secure ? '1' : '0');
        return session_start();
    }
}
