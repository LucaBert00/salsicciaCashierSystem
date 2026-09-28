# Feature Gap Analysis — SalsicciaStagisti → salsicciaCashierSystem

Direzione del confronto: `SalsicciaStagisti` (riferimento) → `salsicciaCashierSystem` (target).
Domanda a cui risponde: **"Cosa può fare oggi SalsicciaStagisti che salsicciaCashierSystem non può fare, oppure non può fare completamente o correttamente?"**
Attività ANALYSIS ONLY: nessuna modifica al codice oltre a questo documento.

---

## 1. Executive Summary

Sintesi evidence-based (codice = fonte primaria, doc = secondaria):

- Le 6 feature recenti di Stagisti (F1 TAGLIA, F2 RIAVVIA/SPEGNI reali, F3 CAMBIA CARTA STAMPA, F4 registry+rinomina stampanti+CAMBA STAMPANTE, F5 `unset admin` #173, F6 MODIFICA FESTA `?action=info`), già mappate come MISSING in `PORTING_ANALYSIS.md` al 2026-09-25, **risultano oggi PORTATE** in CashierSystem (git log target `38a1248→a6d80ff`, closes #51–#76). Verificate una per una nel codice target: bottone/logica TAGLIA, schermate power+preflight+marker, toggle carta+setup `storage/printerCommand/`, pagina switch con `stampante_switch_ok()`, `unset($_SESSION['admin'])` in login, route `info` + `mostraModificaInfo()`. Stato: **PRESENT**.
- Resta **1 solo gap funzionale reale** (PARTIAL, impact LOW): diagnosi testuale stampante non raggiungibile (`printer_motivo_non_raggiungibile()` + `printer_coda_locale_ok()` esistono solo in Stagisti `env.inc:535-559`; le card CAMBIA STAMPANTE di Stagisti mostrano stato/motivo per DIRETTA, quelle di CashierSystem mostrano solo pallino + `CONNESSIONE`). Il gate di selezione è comunque preservato (`stampante_switch_ok()` in CashierSystem fa il check CUPS+USB reale).
- Rilevato **1 link morto solo in CashierSystem** (BROKEN, LOW, direzione inversa — nulla da portare): bottone `STAMPANTE CONTINUA` → `stampante_continua.php`, file inesistente in entrambi i repo.
- Tutto il resto è **PRESENT** o **DIFFERENT IMPLEMENTATION** dove CashierSystem è volutamente più avanti (POST+CSRF per le mutazioni ordine, Argon2id, `storage/` fuori docroot, InnoDB+FK, fail-closed env, fix bug `$mode` diagnostica power, session hardening, throttle file-based). Nessun security gap Stagisti→Cashier: la direzione è opposta.
- Database: stesso baseline (8 tabelle identiche), zero tabelle/colonne funzionali mancanti in CashierSystem; CashierSystem è avanti (migration `0002` login 255ch, `0003` InnoDB+FK+indici). Unico prerequisito noto lato deploy: cutover `0003` bloccato da righe orfane nel live (già documentato in `docs/DATABASE_SCHEMA.md:74-78`), non un porting item.

---

## 2. Repositories Compared

| | SalsicciaStagisti (riferimento) | salsicciaCashierSystem (target) |
|---|---|---|
| Path | `C:\xampp\htdocs\SalsicciaStagisti` | `C:\xampp\htdocs\salsicciaCashierSystem` |
| HEAD verificato | `0632388` Fix printer switch grid (2026-09-25) | `a6d80ff` feat(cassa): route `?action=info` + MODIFICA FESTA (closes #76) |
| Front controller | `index.php` root, if-chain (`$show*`, `index.php:6-18`) | `public/index.php` + tabella `routes/cassa.php` (22 voci) |
| Logica cassa | `funzioni.inc` (1039 righe) + `functionsFrontend.inc` (1731) monolitici | stessi file + `src/` (OrderService, PrintService, Catalog, Backoffice/Tabs, Stats, Fiscale, Backup, Support) |
| Stato runtime | docroot (`label`, `carta_stampa.mode`, `stampante_selezione.json`, `power_attempt.json`) | `storage/` via `salsiccia_storage_path()` |
| Test | script standalone `test_carta_stampa.php`, `test_printer_gate.php` | PHPUnit `tests/` (9 Unit + 1 Integration + fixture) |

---

## 3. Skills Used

Skill invocate via skill tool prima/durante l'analisi (solo quelle concretemente usate):

| Skill | Dove è servita |
|---|---|
| `using-superpowers` | routing obbligatorio a inizio sessione |
| `acquire-codebase-knowledge` | principio "solo affermazioni verificabili da file/diff, niente inferenze"; ha imposto citazioni `file:riga` per ogni voce |
| `domain-modeling` | uso del glossario `CONTEXT.md` (Scontrino vs Ricevuta, Fallback, Coda) per classificare correttamente il fiscale senza confondere ricevuta-etichetta con scontrino |
| `code-review` | confronto Standards-vs-Spec sulle due implementazioni (es. `printer_is_reachable` semplificata vs gate reale: distinto smell da violazione funzionale) |
| `research` | verifica su sorgenti primarie (diff/git log reali, non messaggi di commit) — ha fatto scartare `PORTING_ANALYSIS.md` come verità corrente e ricontrollare ogni F1–F6 nel codice target |
| `systematic-debugging` | tracciamento flusso completo UI→action→funzione→DB→output prima di dichiarare gap (ha evitato il falso positivo "manca `printer_coda_locale_ok` ⇒ selezione rotta": il flusso passa per `stampante_switch_ok()`) |
| `php-pro` | lettura PHP 8.x (enum `OrderType`/`PayMethod`, prepared, `password_verify`/Argon2id, `escapeshellarg`) per giudicare le DIFFERENT IMPLEMENTATION |

---

## 4. Methodology

1. Inventario action: `$_GET['action']`/`$_POST`/switch/fetch in entrambi (22 action Stagisti incl. `taglia=1`; 22 voci `routes/cassa.php` target — copertura 1:1).
2. Per ogni feature: flusso completo UI→action→funzione→include→query→DB→output, in entrambi i repo.
3. Confronto DB: 0 file `.sql` in Stagisti (schema desunto dalle query) vs `database/migrations/0001-0003` + `docs/DATABASE_SCHEMA.md` in CashierSystem.
4. Stampa/auth/frontend: lettura diretta `env.inc`, `set.inc`, `funzioni.inc`/`PrintService.php`, `functionsFrontend.inc`, `style.css`, `.htaccess`, `.env`; grep `fetch/XMLHttpRequest` (0 hit funzionali in entrambi).
5. Git history: `git log --oneline -25` in entrambi + `--since=2026-09-22` in Stagisti; ogni F1–F6 riverificata nel codice target (i commit di porting #51–#76 esistono e il codice c'è).
6. Seconda passata anti-falsi-positivi (§17) prima di chiudere.

Classificazioni usate: `MISSING` / `PARTIAL` / `DIFFERENT IMPLEMENTATION` / `PRESENT` / `BROKEN / NON-FUNCTIONAL` / `UNCERTAIN`.
Impact: `HIGH` (flusso principale/autenticazione/cassa/stampa bloccati), `MEDIUM`, `LOW`.

---

## 5. Global Feature Matrix

| Feature | Stagisti | CashierSystem | Stato | Impact | Evidenza |
|---|---|---|---|---|---|
| Cassa: aggiungi/quantità/rimuovi/annulla ordine | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti GET (`functionsFrontend.inc:299-393`); Cashier POST+CSRF→`OrderService` in txn (`functionsFrontend.inc:223-327`, `src/Cassa/OrderService.php`) |
| Barcode cassa (+ wedge listener) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `GET?action=b&bc=` (`functionsFrontend.inc:265-297`); Cashier `POST bc` via `CatalogRepo::trovaIdPerBarcode` (`functionsFrontend.inc:246-268`) |
| Modifica ordine (mq/mr) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti GET-redirect; Cashier POST+CSRF+txn (`cassa_azione_quantita/rimuovi`) |
| Tipologie ordine (nor/pre/mus/stf/asp) + standby (sb/ra/standby) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti stringhe libere + `UPDATE chiuso` non-scopata (`functionsFrontend.inc:395-445`); Cashier enum `OrderType` + `riattiva` scopata `id_cassa` (`src/Cassa/OrderService.php:217`) |
| TAGLIA a richiesta (`?taglia=1`, solo GX420t+continua) | ✅ | ✅ | PRESENT | — | Stagisti `funzioni.inc:768-774` + `tagliaVisibile()`; Cashier `src/Cassa/PrintService.php:143-149` + `functionsFrontend.inc:483` + CSS `#taglia-btn` |
| RIAVVIA/SPEGNI reali (preflight sudo, marker+uptime, diagnostica) | ✅ | ✅ | PRESENT | — | Stagisti `functionsFrontend.inc:1309-1530`; Cashier `functionsFrontend.inc:739-917` + route `restart/shutdown` (`routes/cassa.php`, `public/index.php:23-24`) + `storage/power_attempt.json` |
| CAMBIA CARTA STAMPA (toggle + setup `printerCommand/`) | ✅ | ✅ | PRESENT | — | Stagisti `env.inc:66-179`, `funzioni.inc:985-1034`, 10 file `printerCommand/`; Cashier stessi + `PrintService::inviaSetupCarta()`, 10 file `storage/printerCommand/` |
| CAMBIA STAMPANTE (registry, selezione persistita, stato CUPS/USB reale) | ✅ | ✅ | PRESENT (gate) / PARTIAL (testo diagnostico, vedi GAP-001) | LOW | Gate+pagina presenti entrambi; manca solo `printer_motivo_non_raggiungibile()`/`printer_coda_locale_ok()` in Cashier `env.inc` |
| Testo diagnostico "motivo non raggiungibile" nelle card switch | ✅ | ❌ | PARTIAL (GAP-001) | LOW | Stagisti `env.inc:535-550` + `functionsFrontend.inc:1193,1240-1246`; Cashier assente (solo pallino + `CONNESSIONE`, `functionsFrontend.inc:1331`) |
| MODIFICA FESTA (`?action=info`, rewrite EVENT_NAME/DURATA_FESTA) | ✅ | ✅ | PRESENT | — | Stagisti `functionsFrontend.inc:1023-1093` + `index.php:18`; Cashier `functionsFrontend.inc` (`mostraModificaInfo`) + route `info` (`routes/cassa.php`, `public/index.php:22`) |
| Auth hardening #173 (`unset admin` a ogni tentativo login) | ✅ | ✅ | PRESENT | — | Stagisti `functionsFrontend.inc:175`; Cashier `cassa_azione_login()` (closes #73, `906a153`) |
| Login/logout admin cassa (throttle, CSRF, regenerate) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `hash_equals` plaintext PWD + throttle sessione; Cashier `password_verify` Argon2id + doppio throttle sessione+file (`env.inc:100-133`) |
| Backoffice login separato (`reserved_auth`, tabella `login`) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti MySQL `PASSWORD()` double-SHA1 (`reserved/login.php:17`); Cashier fetch-then-verify + rehash Argon2id (`reserved/auth_password.inc`) |
| Backoffice CRUD 5 tab (categorie/prodotti/posizioni/contatori/regole) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `reserved/visualizza.php` monolitico (GET del→`AZIONE NON VALIDA`); Cashier `VisualizzaStore`+`Tabs/*Tab.php` (POST-only, blocchi referenziali) |
| Statistiche (incasso/prodotto/fasce/giorni, donut/istogramma SVG, filtri) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `reserved/stat_dati.inc`+`statistiche.php`; Cashier `src/Stats/StatsData.php`+`public/reserved/statistiche.php` (stessi 4 dataset, `chiuso<>0`, giornata fiscale `oraCambio`) |
| Export PDF statistiche (FPDF) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `reserved/fpdf/` vendored 1.6; Cashier `setasign/fpdf:^1.8` via Composer, per-richiesta |
| Backup manuale (mysqldump + fallback PHP, fuori docroot) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `backup.inc` (8 funzioni); Cashier `src/Backup/BackupRun.php` + `storage/dumps/` |
| Fiscale Print!F (build XML IVA, `=T1/=T3`, curl/mock, fallback coda file, retry, idempotenza) | ✅ | ✅ | DIFFERENT IMPLEMENTATION | — | Stagisti `fiscale.inc` (10 funzioni, path docroot); Cashier `src/Fiscale/Fiscale.php` (stessa semantica, path `storage/`) |
| REPAIR TABLE (ripristina DB) | ✅ | ✅ | PRESENT | — | Stagisti `functionsFrontend.inc:969-1019`; Cashier `mostraRipristinaDb()` (`:1031`+) + `SHOW TABLES→REPAIR` |
| Contatori venduti (display paginato) | ✅ | ✅ | PRESENT | — | Stagisti `functionsFrontend.inc:909-965` + `funzioni.inc:376-413`; Cashier `mostraContatori()` + `contatori_totali()` |
| Card degustazione sake (id 14/15/16 → `labelCards`, lpr) | ✅ | ✅ | PRESENT | — | Stagisti `funzioni.inc:415-482,613-627`; Cashier `funzioni.inc:520-587,718-732` (path `storage/labelCards`, `cassa_log`) |
| Menu fisso EPL (id 60-63, `MENU_ENABLE=0` ⇒ mai) | ✅ | ✅ | PRESENT (inattiva entrambi) | — | Stagisti `funzioni.inc:750`; Cashier `PrintService.php:128` |
| Costanti operative (TOTAL_LABEL, PRINT_ORDER_ID, REPORT_CUCINA, DATA_CONFRONTO, REMOTE_CLIENT_IP/ID_CASSA, ONLY_ONE_CATEGORY, DEBUG) | ✅ | ✅ | PRESENT | — | Valori identici (`set.inc` entrambi; `DEBUG 0` entrambi) |
| Bottone `STAMPANTE CONTINUA` → `stampante_continua.php` | ❌ (assente) | ⚠️ (link morto) | BROKEN / NON-FUNCTIONAL (GAP-002, solo target) | LOW | Cashier `functionsFrontend.inc:730`; file inesistente in entrambi i repo (verificato `Test-Path` False + glob vuoto) |
| `printer_is_reachable()` per DIRETTA | ✅ (check CUPS+USB) | ✅ (semplificata: DIRETTA sempre `true`) | DIFFERENT IMPLEMENTATION | — | Stagisti `env.inc:573-578`; Cashier `env.inc:367-375`. Gate UI preservato via `stampante_switch_ok()` (`functionsFrontend.inc:1244-1252`, check CUPS+USB reale) |
| `mostraDiagnosticaPower()` | ⚠️ (bug: `$mode` indefinito riga ~1452) | ✅ (fix: `$mode` parametro, `:907`) | DIFFERENT IMPLEMENTATION | — | Cashier corregge il bug upstream (PORTING_ANALYSIS §5-F2) |
| Test (standalone vs PHPUnit) | ✅ (2 script) | ✅ (9 Unit + 1 Integration + fixture) | DIFFERENT IMPLEMENTATION | — | Cashier copre parser/gate/carta/power/throttle/order/fiscale-retry |
| Docs tecniche | magre | ✅ (PORTING_ANALYSIS, DATABASE_SCHEMA, INC_REVIEW, FPDF_AUDIT, LEGACY_GRUPPO5) | DIFFERENT IMPLEMENTATION | — | Cashier avanti; nessun doc Stagisti manca
...[truncated 14068 chars]
