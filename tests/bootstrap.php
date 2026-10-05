<?php

declare(strict_types=1);

// PHPUnit bootstrap: composer autoload (helpers csrf/db via files) + classi
// PSR-4 sotto test (Fiscale/StatsData) + LabelBuilder per i builder puri
// (F4.4 #101, nuove sedi namespaced). Nessun DB, nessuna rete, nessun hardware.
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Fiscale/Fiscale.php';
require_once __DIR__ . '/../src/Cassa/LabelBuilder.php';
require_once __DIR__ . '/../src/Stats/StatsData.php';
