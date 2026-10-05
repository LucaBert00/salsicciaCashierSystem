<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// Override solo per i test: i doppi di calcolaTotali non sono mysqli_result e
// le procedurali globali li rifiuterebbero (stesso motivo del fetch OO di
// CatalogRepo::trovaIdPerBarcode). Delega al globale per mysqli_result veri.
if (!function_exists('Salsiccia\Cassa\mysqli_num_rows')) {
    function mysqli_num_rows($res): int
    {
        if ($res instanceof \mysqli_result) {
            return \mysqli_num_rows($res);
        }
        if (is_object($res) && isset($res->righe) && is_array($res->righe)) {
            return count($res->righe);
        }
        return 0;
    }
}

if (!function_exists('Salsiccia\Cassa\mysqli_fetch_array')) {
    function mysqli_fetch_array($res, $mode = MYSQLI_BOTH): array|null|false
    {
        if ($res instanceof \mysqli_result) {
            return \mysqli_fetch_array($res, $mode);
        }
        if (is_object($res) && method_exists($res, 'fetch_array')) {
            return $res->fetch_array();
        }
        return null;
    }
}
