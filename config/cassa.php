<?php

declare(strict_types=1);

// config/cassa.php — defaults inerti cassa (T17, rif. docs/ARCHITETTURA_REVISTA.md §2 P5, §6 e §9 fase 6).
// Mai scritto a runtime: solo lettura. Lo stato mutabile (MODALITA_FIERA) vive in
// storage/cassa_flags.json via cassa_leggi_fiera()/cassa_imposta_fiera() (env.inc).
// NOTO: set.inc è ancora riscritto a runtime da mostraModificaInfo()
// (functionsFrontend.inc) per EVENT_NAME/DURATA_FESTA — invariante NON rispettata,
// v. docs/ARCHITETTURA_REVISTA.md §2 P4. Fix previsto in fase 0 (storage/festa.json).
return array(
    'MODALITA_FIERA' => '0',
);
