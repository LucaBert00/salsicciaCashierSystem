<?php

declare(strict_types=1);

namespace Salsiccia\Stats;

// Override solo per i test: il doppio di contatori_totali non e' mysqli_result
// (stesso motivo dell'override Salsiccia\Cassa per calcolaTotali). Delega al
// globale per mysqli_result veri.
if (!function_exists('Salsiccia\Stats\mysqli_fetch_array')) {
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
