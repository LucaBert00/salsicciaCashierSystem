<?php

declare(strict_types=1);

namespace Salsiccia\Config;

// Info festa su storage/festa.json (F6.1 #109,
// docs/ARCHITETTURA_REVISTA.md §10 punto 21 + §6 riga set.inc
// + §9 target + §7 file nuovi con declare(strict_types=1)).
// Delega pura a CassaFlags::festaLeggi()/festaImposta() (stessi path JSON,
// stessa forma del JSON, stessa semantica atomica tmp+rename con LOCK_EX
// pattern F0.3/T17): nessuna seconda implementazione, mai file_put_contents
// su sorgente PHP. I consumer migrano in F6.2.
final class FestaConfig
{
    public static function leggi(): array
    {
        return CassaFlags::festaLeggi();
    }

    /**
     * @param mixed $eventName
     * @param mixed $durataFesta
     */
    public static function imposta($eventName, $durataFesta): void
    {
        CassaFlags::festaImposta($eventName, $durataFesta);
    }
}
