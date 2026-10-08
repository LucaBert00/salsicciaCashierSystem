<?php

declare(strict_types=1);

namespace Salsiccia\Support;

// Facade log + loader .env della cassa (F5.1 #103,
// docs/ARCHITETTURA_REVISTA.md §10 punto 19 + §6 1-30 + §9 target).
// Corpi verbatim da env.inc:1-30: cassa_log() sopra error_log (solo codici,
// mai payload) + loader .env con precedenza alle env reali (sistema/Apache).
// Unica casa di cassa_log() in src/Support (gli altri moduli F5 la riusano,
// mai duplicata qui). Metodi statici: nessun stato, autoload PSR-4 senza
// toccare composer.json. Transitorio onesto: env.inc resta dov'e con le
// funzioni originali ancora presenti (delete solo in F5.5); nessun consumer
// migrato qui (dbConnect.php, public/reserved/*.php, SessionThrottleTest
// invariati). Radice risolta via dirname(__DIR__, 2): stesso file .env di
// env.inc (l'unica differenza di riga oltre a namespace/firma).
final class Env
{
    public static function log(string $level, string $message): void
    {
        $level = strtolower($level);
        if (!in_array($level, array('debug', 'info', 'warning', 'error'), true)) {
            $level = 'info';
        }
        error_log('cassa [' . $level . '] ' . $message);
    }

    // Carica .env (KEY=VALORE) nelle env di processo; le env reali
    // (sistema/Apache) hanno precedenza.
    public static function carica(): void
    {
        $f = dirname(__DIR__, 2) . '/.env';
        if (is_readable($f)) {
            $parsed = parse_ini_file($f, false, INI_SCANNER_RAW);
            if ($parsed === false) {
                self::log('warning', 'env parse fallito');
            }
            foreach ((array)($parsed === false ? array() : $parsed) as $k => $v) {
                if (getenv($k) === false) {
                    putenv("$k=$v");
                    $_ENV[$k] = $v;
                }
            }
        }
    }
}
