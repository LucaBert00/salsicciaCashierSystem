<?php
declare(strict_types=1);
// bootstrap.php — unico punto di inizializzazione degli entry point public/*
// (F3.1 + F3.2, docs/ARCHITETTURA_REVISTA.md §10 punto 12 + §4).
// Sostituisce i require concatenati nei 6 entry point e rende visibile
// l'ordine: autoload Composer (Db + helpers csrf/db via files), ErrorHandler,
// set.inc (fail-closed, puo rispondere 500), dbConnect.php (fail-closed, esce
// se manca env), functionsFrontend.inc (F3.2: solo definizioni, zero
// side-effect al require), Session::start().
// F5.5b #118: la catena d'ingresso non chiama piu i globali di env.inc: set.inc,
// dbConnect.php e helpers.php usano i metodi statici dei moduli F5 e il loader
// .env e' esplicito (Env::carica() dentro i due file che leggono getenv).
// L'unico require di env.inc rimasto e' in functionsFrontend.inc, che e' il
// consumer legacy ancora da migrare (#120); sparisce con #121.
// Transitorio onesto: riusa gli .inc restanti cosi come sono; nessuna classe nuova
// (CassaConfig, Printer/*, System/* sono F6/F7) e nessun delete legacy oltre
// F4.4 (§9: solo a migrazione completa).
// functionsFrontend.inc isolabile senza DB (F3.2 #95).
// Passo futuro (§4): CassaConfig::carica(), Db::connetti().
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Support/ErrorHandler.php';
\Salsiccia\Support\ErrorHandler::registra();
require_once __DIR__ . '/set.inc';
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/functionsFrontend.inc';
\Salsiccia\Support\Session::start();
require_once __DIR__ . '/src/Cassa/CassaController.php';
require_once __DIR__ . '/src/Cassa/CassaView.php';
