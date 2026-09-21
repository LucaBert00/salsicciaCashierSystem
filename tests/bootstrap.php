<?php

declare(strict_types=1);

// PHPUnit bootstrap: composer autoload + classi PSR-4 sotto test
// (Fiscale/StatsData) + funzioni.inc legacy per i builder puri restanti
// (etichetta_continua). Nessun DB, nessuna rete, nessun hardware.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Fiscale/Fiscale.php';
require_once __DIR__ . '/../funzioni.inc';
require_once __DIR__ . '/../src/Stats/StatsData.php';
