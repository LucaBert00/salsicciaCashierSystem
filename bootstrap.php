<?php
declare(strict_types=1);
// bootstrap.php — unico punto di inizializzazione degli entry point public/*
// (F3.1 + F3.2, docs/ARCHITETTURA_REVISTA.md §10 punto 12 + §4).
// Sostituisce i require concatenati nei 6 entry point e rende visibile
// l'ordine: autoload Composer, ErrorHandler, set.inc (fail-closed, puo
// rispondere 500), dbConnect.php (fail-closed, esce se manca env),
// funzioni.inc, functionsFrontend.inc (F3.2: solo definizioni, zero
// side-effect al require), salsiccia_session_start().
// Transitorio onesto: riusa gli .inc cosi come sono; nessuna classe nuova
// (Env/Storage/Session/Throttle, Db, CassaConfig, Printer/*, System/*,
// LabelBuilder sono F4/F5/F6) e nessun delete legacy (§9: solo a migrazione
// completa). functionsFrontend.inc isolabile senza DB (F3.2 #95).
// Passo futuro (§4): Env::carica(), CassaConfig::carica(), Session::start(),
// Db::connetti().
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Support/ErrorHandler.php';
\Salsiccia\Support\ErrorHandler::registra();
require_once __DIR__ . '/set.inc';
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/funzioni.inc';
require_once __DIR__ . '/functionsFrontend.inc';
salsiccia_session_start();
require_once __DIR__ . '/src/Cassa/CassaController.php';
require_once __DIR__ . '/src/Cassa/CassaView.php';
