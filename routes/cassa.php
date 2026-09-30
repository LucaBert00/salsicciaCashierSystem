<?php

declare(strict_types=1);

// routes/cassa.php — tabella action=>handler|view|auth cassa (F1.1, ex T23).
// Rif. docs/ARCHITETTURA_REVISTA.md §4 (tabella target) + §10 punto 4 + §3.2a-b.
// Mappa F1 #82: docs in sola lettura, mai modificare.
//
// Nessuna logica: solo dati. Unica fonte di verita per validita
// (public/index.php:9-10 via array_key_exists), viste (ex 12 boolean
// index.php:12-24) e autorizzazione (ex lista 7 action index.php:36).
return array(
    // Mutazioni: handler in functionsFrontend.inc (guard POST+CSRF invariati,
    // T14; flussi ordine in OrderService, T18); view = destinazione odierna.
    'c' => array('handler' => 'cassa_azione_login', 'view' => 'config', 'auth' => null),
    'logout' => array('handler' => 'cassa_azione_logout', 'view' => 'cassa', 'auth' => null),
    'cassa' => array('handler' => 'cassa_azione_cassa', 'view' => 'config', 'auth' => null),
    'fiera' => array('handler' => 'cassa_azione_fiera', 'view' => 'cassa', 'auth' => null),
    'r' => array('handler' => 'cassa_azione_annulla', 'view' => 'cassa', 'auth' => null),
    'a' => array('handler' => 'cassa_azione_aggiungi', 'view' => 'cassa', 'auth' => null),
    'b' => array('handler' => 'cassa_azione_barcode', 'view' => 'cassa', 'auth' => null),
    'mq' => array('handler' => 'cassa_azione_quantita', 'view' => 'modifica', 'auth' => null),
    'mr' => array('handler' => 'cassa_azione_rimuovi', 'view' => 'modifica', 'auth' => null),
    'st' => array('handler' => 'cassa_azione_tipo', 'view' => 'opzioni', 'auth' => null),
    'sb' => array('handler' => 'cassa_azione_standby', 'view' => 'cassa', 'auth' => null),
    'ra' => array('handler' => 'cassa_azione_riattiva', 'view' => 'cassa', 'auth' => null),
    // Schermate (viste ex public/index.php:12-24; auth ex lista 7 index.php:36).
    'm' => array('handler' => null, 'view' => 'modifica', 'auth' => null),
    'o' => array('handler' => null, 'view' => 'opzioni', 'auth' => null),
    's' => array('handler' => null, 'view' => 'resto', 'auth' => null),
    'standby' => array('handler' => null, 'view' => 'standby', 'auth' => null),
    'repair' => array('handler' => null, 'view' => 'repair', 'auth' => 'admin'),
    'contatori' => array('handler' => null, 'view' => 'contatori', 'auth' => 'admin'),
    'print_reset' => array('handler' => null, 'view' => 'printReset', 'auth' => 'admin'),
    'restart' => array('handler' => null, 'view' => 'restart', 'auth' => 'admin'),
    'shutdown' => array('handler' => null, 'view' => 'shutdown', 'auth' => 'admin'),
    'switch_printer' => array('handler' => 'salvaStampante', 'view' => 'switchPrinter', 'auth' => 'admin'),
    'info' => array('handler' => 'salvaFesta', 'view' => 'info', 'auth' => 'admin'),
);
