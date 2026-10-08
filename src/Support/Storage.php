<?php

declare(strict_types=1);

namespace Salsiccia\Support;

// Cartella storage fuori docroot per spool/coda/log (F5.1 #103,
// docs/ARCHITETTURA_REVISTA.md §10 punto 19 + §6 34-46 + §9 target).
// Corpi verbatim da env.inc:34-46 (stessi path, stesso basename anti-traversal,
// stessa mkdir 0770). Metodi statici: nessun stato, autoload PSR-4 senza
// toccare composer.json. Transitorio onesto: env.inc resta dov'e con le
// funzioni originali ancora presenti (delete solo in F5.5); nessun consumer
// migrato qui. Radice risolta via dirname(__DIR__, 2): stessa storage/ di
// env.inc (l'unica differenza di riga oltre a namespace/firma).
final class Storage
{
    public static function dir(): string
    {
        $d = dirname(__DIR__, 2) . '/storage';
        if (!is_dir($d)) {
            mkdir($d, 0770, true);
        }
        return $d;
    }

    // Path assoluto in storage/ per nomi spool/coda/log (solo basename, mai traversal).
    public static function path(string $name): string
    {
        return self::dir() . '/' . basename($name);
    }
}
