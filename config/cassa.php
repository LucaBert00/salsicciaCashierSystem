<?php

declare(strict_types=1);

// config/cassa.php — defaults inerti cassa (T17, rif. docs/ARCHITETTURA_REVISTA.md §2 P5, §6 e §9 fase 6).
// Mai scritto a runtime: solo lettura. Lo stato mutabile (MODALITA_FIERA) vive in
// storage/cassa_flags.json via CassaFlags::cassaLeggiFiera()/cassaImpostaFiera()
// (src/Config/CassaFlags.php); EVENT_NAME/DURATA_FESTA vivono in
// storage/festa.json via CassaFlags::festaLeggi()/festaImposta()
// (stessa semantica atomica tmp+rename).
// NOTO: set.inc è ancora riscritto a runtime da mostraModificaInfo()
// (functionsFrontend.inc) per EVENT_NAME/DURATA_FESTA — invariante NON rispettata,
// v. docs/ARCHITETTURA_REVISTA.md §2 P4. Fix in F6.2/F6.3 (FestaConfig, delete set.inc).
// F6.1 #109: qui solo i default delle vive (stessi valori di set.inc), mai logica,
// mai getenv()/die(), mai scrittura a runtime. Le costanti morte
// (docs/ARCHITETTURA_REVISTA.md §5, 7 voci) non compaiono nel modello
// (MODALITA_FIERA resta solo come default inerte + flag JSON T17).
// Fail-closed SALSICCIA_PRINTER_IP/SALSICCIA_ADMIN_PWD_HASH
// restano in env/set.inc, mai duplicati qui. LABELS_FILE/COMMAND_FILE sono nomi
// relativi a storage/ (CassaConfig::carica() li risolve via Storage, byte-identici
// ai defined() di set.inc); PRINTER_IP vuoto = non configurato (il die(500)
// resta in set.inc fino al delete F6.3, mai duplicato qui).
return array(
    'MODALITA_FIERA' => '0',
    'ONLY_ONE_CATEGORY' => 0,
    'BOT_X_COL' => 4,
    'BOT_X_ROW' => 6,
    'BOT_WIDTH' => 205,
    'BOT_HEIGHT' => 120,
    'ZPL_DOTS_PER_MM' => 8,
    'ORA_CAMBIO_DATA' => '5',
    'TIPO_ORDINE' => 'nor',
    'ID_CASSA' => 1,
    'DEFAULT_CAT' => 19,
    'DEBUG' => 0,
    'PRINT_ORDER_ID' => '0',
    'LABELS_FILE' => 'label',
    'COMMAND_FILE' => 'cmd/cmd',
    'PRINTER_NAME' => 'ZD230',
    'PRINTER_CONNECTION' => 'RETE',
    'PRINTER_LANGUAGE' => 'ZPL',
    'PRINTER_IP' => '',
    'CONTINUOUS_LABEL' => 1,
    'CREDITS' => 'T-System SALSICCIA - Datrik Solutions',
);
