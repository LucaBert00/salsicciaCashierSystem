<?php

declare(strict_types=1);

// PHPUnit bootstrap: composer autoload (per Salsiccia\Cassa\PayMethod usato da
// fiscale.inc) + gli .inc legacy con le funzioni pure sotto test. Nessun DB,
// nessuna rete, nessun hardware.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../fiscale.inc';
require_once __DIR__ . '/../funzioni.inc';
require_once __DIR__ . '/../reserved/stat_dati.inc';
