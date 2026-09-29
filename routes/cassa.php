<?php

declare(strict_types=1);

// routes/cassa.php — tabella action→handler cassa (T23).
// Rif. docs/ARCHITETTURA_REVISTA.md §2 (P2 routing triplicato), §4 (estensione
// proposta a handler|view|auth: una riga per schermata, una sola fonte di verità).
// Mappa #10: mai modificare docs/ARCHITETTURA_REVISTA.md (sola lettura).
//
// Nessuna logica: solo dati. Dispatch mutazioni in gestisciAzioni()
// (functionsFrontend.inc); validita schermate in public/index.php (action non
// listata = schermata errore kiosk, mai fatal). Futura casa di Http/
// middleware (Auth, Csrf) quando il front controller crescera.
return array(
    // Mutazioni: action => handler in functionsFrontend.inc (guard POST+CSRF
    // invariati, T14; flussi ordine in OrderService, T18).
    'c' => 'cassa_azione_login',
    'logout' => 'cassa_azione_logout',
    'cassa' => 'cassa_azione_cassa',
    'fiera' => 'cassa_azione_fiera',
    'r' => 'cassa_azione_annulla',
    'a' => 'cassa_azione_aggiungi',
    'b' => 'cassa_azione_barcode',
    'mq' => 'cassa_azione_quantita',
    'mr' => 'cassa_azione_rimuovi',
    'st' => 'cassa_azione_tipo',
    'sb' => 'cassa_azione_standby',
    'ra' => 'cassa_azione_riattiva',
    // Schermate sola lettura (viste in public/index.php): listate, nessun handler.
    'm' => null,
    'o' => null,
    's' => null,
    'standby' => null,
    'repair' => null,
    'contatori' => null,
    'print_reset' => null,
    'restart' => null,
    'shutdown' => null,
    'switch_printer' => null,
    'info' => null,
);
