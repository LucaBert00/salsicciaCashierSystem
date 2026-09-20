<?php
declare(strict_types=1);
// config/cassa.php — defaults inerti cassa (T17, rif. PHP_STRUCTURE_REVIEW.md §4 Major row 9 / §5 config row / §8-10).
// Mai scritto a runtime: solo lettura. Lo stato mutabile (MODALITA_FIERA) vive in
// storage/cassa_flags.json via cassa_leggi_fiera()/cassa_imposta_fiera() (env.inc).
// set.inc resta congelato ai default sotto (read-only, mai rewrite).
return array(
    'MODALITA_FIERA' => '0',
);
