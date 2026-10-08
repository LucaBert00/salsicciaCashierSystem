<?php

declare(strict_types=1);

namespace Salsiccia\System;

// F2.2 #89: logica REPAIR senza HTML (ramo POST di mostraRipristinaDb(),
// functionsFrontend.inc:1019-1032, mappa §6 1023-1056).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 9 + §6 + §9 + §3.2b (sola lettura).
// Pura: solo $db, mai echo/header/$_POST/$_GET/sessione/isAdmin (restano nel chiamante).
// Whitelist identica a oggi: /^[A-Za-z0-9_]+$/, backtick, Env::log warning + continue.
final class DbRepair
{
    /**
     * @param mixed $db mysqli reale o doppio di test con superficie query()/error
     * @return array<int, array{table: string, ok: bool, error: string}>
     */
    public static function run($db): array
    {
        $righe = array();
        $ris = $db->query('SHOW TABLES');
        if ($ris === false) {
            return $righe;
        }
        while ($row = $ris->fetch_array()) {
            $table = (string)$row[0];
            if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
                \Salsiccia\Support\Env::log('warning', 'REPAIR bloccato: tabella non whitelistata');
                continue;
            }
            $ok = $db->query('REPAIR TABLE `' . $table . '`');
            $righe[] = array('table' => $table, 'ok' => (bool)$ok, 'error' => $ok ? '' : (string)$db->error);
        }
        return $righe;
    }
}
