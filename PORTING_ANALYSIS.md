# PORTING_ANALYSIS — salsicciaStagisti → salsicciaCashierSystem

Analisi feature-porting, evidence-based su Git-history + diff + codice corrente.
Scope: solo cosa è stato aggiunto a `salsicciaStagisti` **dal 2026-09-22 in poi** e cosa va portato in `salsicciaCashierSystem`. Nessuna implementazione eseguita, nessun giudizio architetturale generale.

Skills usate: `using-superpowers` (routing obbligatorio), `acquire-codebase-knowledge` (principi: solo affermazioni verificabili da file/diff, niente inferenze), `research` (verifica su sorgenti primarie — i diff reali, non i messaggi di commit).

---

## 1. Executive Summary

Nel periodo **2026-09-22 → 2026-09-25** `salsicciaStagisti` ha ricevuto **38 commit** che implementano **6 feature funzionali** (tutte assenti da `salsicciaCashierSystem`) più una serie di commit intermedi di iterazione/cleanup/commenti **da non portare**.

| # | Feature | Stato in CashierSystem |
|---|---------|------------------------|
| F1 | Pulsante **TAGLIA** (taglio carta GX420t a richiesta, `?action=s&taglia=1`) | **Missing** |
| F2 | **RIAVVIA / SPEGNI reali** da pannello admin (shutdown schedulato + preflight sudo + riscontro marker/uptime) | **Missing** (i bottoni esistono ma sono link morti) |
| F3 | **CAMBIA CARTA STAMPA** (toggle continua↔singoli persistito + setup inviato alla stampante via file `printerCommand/`) | **Missing** (c'è ancora il vecchio PRINT RESET `~JA/~JR`) |
| F4 | **CAMBIA STAMPANTE** (registry stampanti, selezione persistita, pagina `switch_printer` con stato CUPS/USB veritiero) + **rinomina stampanti** (`Zebra_*` → `ZD230/GX420t/TLP2844/LP2844`) | **Missing** |
| F5 | **Auth hardening #173**: invalidazione sessione admin a ogni tentativo di login | **Missing** (1 riga) |
| F6 | **MODIFICA FESTA** (schermata `?action=info` che riscrive `EVENT_NAME`/`DURATA_FESTA` in `set.inc`) | **Missing** |

- Feature già presenti in CashierSystem: **0** (nessuna delle 6 esiste, nemmeno parzialmente — fa eccezione F5 che è 1 riga e i bottoni admin morti di F2).
- Scope di porting: **6 feature, ~15 file target**, zero nuove variabili `.env` richieste (tutte le env usate pre-esistono), zero migrazioni DB.
- Dipendenza critica: **F4 (registry + rinomina nomi stampanti) va portato per primo**, perché F1, F2 e F3 usano i nuovi nomi (`GX420t`, `ZD230`, …) e le funzioni `printer_*` di `env.inc`.

---

## 2. Git Comparison Baseline

### Target — `salsicciaCashierSystem` (branch `master`)

- Ultimo commit: `7ba9ab9` — `feat(backoffice): port optional-barcode prodotti tab, works without barcode column (closes #47)`
- Data: **2026-09-21 22:15:57 +0200**
- I 3 commit precedenti (`d03459d` #45 nav style, `f2f7772` #46 barcode wedge, `7ba9ab9` #47 optional barcode) sono **port già avvenuti da stagisti** — confermano che il punto di divergenza è reale e che il meccanismo "porta da stagisti" è già in uso.

### Source — `salsicciaStagisti` (branch `master`)

- Ultimo commit: `0632388` — `Fix printer switch grid` — **2026-09-25 17:38:37 +0200**
- Range analizzato: **2026-09-22 00:00:00 → HEAD (2026-09-25)**: 38 commit.
- Diff cumulato `4f1385a~1..HEAD`: **27 file, 1676 inserimenti, 752 rimozioni**. Al netto di commenti/doc, il funzionale tocca: `env.inc` (+~540 righe: registry + CUPS + carta-stampa), `functionsFrontend.inc` (+~500: 4 nuove schermate admin + TAGLIA + login), `funzioni.inc` (taglia + `cartaStampaInviaContenuto` + `printer_cups_queue` in `lpr`), `set.inc` (selezione persistita + `carta_stampa.mode`), `index.php` (4 nuove action), `style.css` (taglia + admin-msg-big + switch grid), `printerCommand/` (10 file setup, schema finale), `.gitignore` (3 state-file).

### Differenze strutturali rilevanti (solo per il porting)

| Area | Stagisti (source) | CashierSystem (target) — vincolo di porting |
|------|-------------------|----------------------------------------------|
| Front controller | `index.php` in root, if-chain | `public/index.php` + tabella validazione `routes/cassa.php` (ogni nuova action va aggiunta in **entrambi**) |
| Azioni cassa | `gestisciAzioni()` monolitica | handler `cassa_azione_*` in `functionsFrontend.inc` (nuove schermate = nuove funzioni `mostra*`, nessun handler serve tranne dove c'è POST dedicato — ma i POST sono gestiti inline nelle `mostra*`, vedi sotto) |
| Pipeline stampa | `funzioni.inc`: `genera_file_stampa` + `invia_file_stampa` + `ftpPut` | split T32: spool/invio in `src/Cassa/PrintService.php` (`generaFileStampa`, `inviaFileStampa`, `ftpPut`); builder (`etichetta_continua`, `get_product_label`) restano in `funzioni.inc` |
| Logging | `error_log()` diretto | facade `cassa_log()` (`src/Support/` + `funzioni.inc` shim) — **ogni `error_log` portato va convertito** |
| File di stato runtime | `__DIR__` (root docroot): `carta_stampa.mode`, `stampante_selezione.json`, `power_attempt.json` | docroot = `public/` → gli state-file vanno in `storage/` via `salsiccia_storage_path()` (`env.inc:34-43`) + righe `.gitignore`. **Non copiare i path `__DIR__`.** |
| CSS | `style.css` in root | `public/style.css` |
| Config fail-closed | `PRINTER_IP` con fallback `192.168.0.205` | `set.inc:52-58`: **die 500 se `SALSICCIA_PRINTER_IP` vuota** — mantenere; la selezione persistita fornisce solo override IP per RETE |
| Test | script standalone `test_*.php` in root | harness PHPUnit `tests/` (T27) — gli script stagisti sono **riferimento, non da copiare** (vedi §5, F3/F4) |

---

## 3. Commit-by-Commit Analysis

Legenda impatto: **PORT** = contiene funzionale da portare · **ITER** = iterazione intermedia (stato finale coperto da commit successivi, non portare singolarmente) · **SKIP** = non portabile (chore/commenti/deploy-state/WIP superato).

### Gruppo A — TAGLIA (F1)

| Commit | Data | Messaggio | File | Proposito reale (dal diff) | Impatto |
|--------|------|-----------|------|----------------------------|---------|
| `4f1385a` | 09-22 | Aggiunta stampante con comando TAGLIA | env, functionsFrontend, funzioni, set, style | 1ª versione: stampante `Zebra_GX420t` (RETE+ZPL), `printer_has_cutter`/`printer_taglia_permesso`, bottone TAGLIA nei footer. **Nomi e combo superati** dai commit successivi; flip `MODALITA_FIERA` 0→1 (deploy fiera). | **ITER** (concetto sì, codice no) + deploy-state da non portare |
| `b392091` | 09-24 | wip: snapshot working tree pre #169 (stub taglia + gate showStampa) | funzioni, index | Stub **rotto** (`if(action == )` committato) + `$showStampa` gatato su `isset($_GET['taglia'])` (poi revertito: HEAD ha `$showStampa = $action == 's'`). | **SKIP** (WIP superato, mai portare) |
| `8e569f8` | 09-24 | fix(taglia): flag 0/1 + append ~JK in coda stampa GX420t (#170) | functionsFrontend, funzioni, index | Taglio a richiesta via `?taglia=1` con append `~JK`. Meccanismo giusto, comando poi corretto. | **ITER** |
| `686bf17` | 09-24 | docs(taglia): nota C vs ~JK su append taglio GX420t | funzioni | Solo commento. | **SKIP** |
| `8cec1d0` | 09-24 | chore: if naturali per append taglia, rimuove FINDINGS-switch_printer.md | funzioni (+delete md) | Solo commenti + delete doc. | **SKIP** |
| `c38226e` | 09-25 | fix(taglia): taglio EPL con C a richiesta, niente più auto-taglio continuo | functionsFrontend, funzioni | **Funzionale**: rimozione `C` automatico da `etichetta_continua` (`P1\nC\n` → `P1\n`); taglio solo a richiesta. | **PORT** (parte di F1) |
| `0a7c38e` | 09-25 | fix(taglia): taglio ZPL con ^MMC dentro ^XA, rimuove ~JK | funzioni | **Funzionale**: ZPL `^MMC` iniettato dopo `^XA` (al posto di `~JK`). Stato finale del comando. | **PORT** (parte di F1) |

### Gruppo B — Alimentazione RIAVVIA/SPEGNI (F2)

| Commit | Data | Messaggio | File | Proposito reale | Impatto |
|--------|------|-----------|------|-----------------|---------|
| `57d8298` | 09-23 | Fix #146: RIAVVIA/SPEGNI reali da pannello admin (POST+CSRF+confirm, solo admin) | functionsFrontend, index | **Funzionale**: `svuotaCodaStampaPreSpegnimento()` (`cancel -a` best-effort in DIRETTA), `schedulaAzioneAlimentazione()` (`sudo /sbin/shutdown`, sleep 2 + background), `mostraPower()`/`mostraRestart()`/`mostraShutdown()`, route `restart`/`shutdown` + gate admin in `index.php`. | **PORT** (base F2) |
| `6b796de` | 09-23 | Fix #148: SPEGNI/RIAVVIA mai verde falso (preflight shutdown+sudo, rosso COMANDO NON AVVIATO) | functionsFrontend, set | **Funzionale**: `candidatiAlimentazione()` (legacy-first systemctl→shutdown), `escapaComando()`, `scegliComandoAlimentazione()` (preflight `sudo -n -l`); **flip `MODALITA_FIERA` 1→0** (deploy-state). | **PORT** (codice; il flip fiera NO) |
| `11ba435` | 09-23 | style(power/print_reset): scritte grandi uniformi 20px… | functionsFrontend, style | Classe `.admin-msg-big` (clamp 20px→27px) + applicazione alle 3 schermate. Puro stile ma **dipendenza di F2/F3/F4/F6** (tutte le nuove schermate usano la classe). | **PORT** (solo la classe CSS + suo uso) |
| `d57a201` | 09-23 | Fix #149: riscontro reale SPEGNI/RIAVVIA (marker + uptime + diagnostica) | .gitignore, functionsFrontend | **Funzionale**: `power_attempt.json` (marker), `uptimeMacchina()` (/proc/uptime), `esitoTentativoPower()` pura (riuscito/fallito/attesa, soglia 180s), `mostraRiscontroPower()`, `mostraDiagnosticaPower()`. **BUG upstream**: `mostraDiagnosticaPower()` usa `$mode` non definito (riga ~1452: `$cmd = scegliComandoAlimentazione($mode);`) — in PHP 8 è `null`, quindi la diagnostica mostra sempre i candidati `reboot`. Nel port passare `$mode` come parametro. | **PORT** (con fix del bug) |

### Gruppo C — Carta stampa (F3)

| Commit | Data | Messaggio | File | Proposito reale | Impatto |
|--------|------|-----------|------|-----------------|---------|
| `67379f1` | 09-23 | Fix #147: PRINT RESET diventa CAMBIA CARTA STAMPA (toggle continua/singoli persistente) | .gitignore, functionsFrontend, index, set | **Funzionale**: `mostraPrintReset()` riscritta (toggle, POST+CSRF+confirm), `impostaCartaStampa()` (`carta_stampa.mode`), precedenza `set.inc`: env > file > gate; label bottone admin con modo corrente. | **PORT** (base F3) |
| `74a7a09` | 09-25 | chore(printerCommand): aggiunge istruzioni stampa continua e singoli | printerCommand/ (19 file grezzi) | Dump iniziale file setup (nomi ad-hoc). Superato dallo schema finale. | **ITER** |
| `a79ed89` | 09-25 | feat(carta-stampa): applica file printerCommand al toggle continua-singoli (#172) | env, functionsFrontend, funzioni, +`test_carta_stampa.php` | **Funzionale**: `cartaStampaFilePerStampante()` (mappa combo→file), `cartaStampaNormalizzaContenuto()` (EPL: `q464/q832` + riga `Q`; ZPL: `^XA..^XZ` + `^PW464/^PW832`; rifiuta vuoto/>8KB/NUL), `cartaStampaLeggiSetup()` (`$baseDir` iniettabile per test), `cartaStampaInviaContenuto()` (lpr/FTP su file temporaneo, mai LABELS_FILE). | **PORT** (core F3) |
| `f213d06` | 09-25 | chore(printerCommand): rimuove 12 file non in whitelist, fixture q600 inline nel test | printerCommand, test | Cleanup. | **SKIP** (assorbito in F3) |
| `5686e34` | 09-25 | refactor(printerCommand): schema cmd_[STAMPANTE]_[SINGOLI\|CONTINUA]_[EPL\|ZPL], 10 file uno per combo | env, printerCommand, test | **Funzionale**: schema nomi finale (10 file, uno per combo permessa); larghezze fisse singoli `q464`/`^PW464`, continua `q832`/`^PW832`. | **PORT** (nomi file + contenuti F3) |

### Gruppo D — Switch stampante + rinomina (F4)

| Commit | Data | Messaggio | File | Proposito reale | Impatto |
|--------|------|-----------|------|-----------------|---------|
| `80dfe5c` | 09-23 | T1-research #161: findings switch_printer | FINDINGS md | Solo research doc (file poi cancellato). | **SKIP** |
| `45ed795` | 09-23 | T2-task #162: registry stampanti, persistenza selezione, gate GX420t dual, ping raggiungibilità | .gitignore, env, functionsFrontend, funzioni, index, set | **Funzionale**: `printer_known_printers()`, `printer_selection_file()`/`printer_leggi_selezione()` (+ lettura a boot in `set.inc`), `impostaStampanteSelezionata()`, `printer_ping()`, `printer_is_reachable()`, validazione IP. | **PORT** (base F4) |
| `b3eca1a` | 09-23 | Appunto #162: GX420t solo DIRETTA dual come Multi, Net unica anche in RETE | FINDINGS, env, funzioni, set | Appunto + aggiustamento gate intermedio (nomi ancora `Zebra_*`). Superato da `2a7a3ae`+`5fe9f0d`. | **ITER** |
| `2a7a3ae` | 09-24 | Zebra_Multi -> Zebra_GX420t unica dual ZPL+EPL | FINDINGS, env, funzioni, set, +`test_printer_gate.php` | **Funzionale**: rinomina 1ª fase + test standalone del gate. | **PORT** (assorbito in F4 finale) |
| `284a344` | 09-24 | T3-task #163: bottone CAMBIA STAMPANTE + pagina switch_printer… | env, functionsFrontend, index | **Funzionale**: bottone admin, route `switch_printer`, `mostraSwitchPrinter()` v1. | **PORT** (assorbito in F4 finale) |
| `9cfb30b` | 09-24 | T3-task #163: SELEZIONA+radio per GX420t, card leggibili… | env, functionsFrontend | UX pagina (radio ZPL/EPL solo GX420t, DIRETTA non verificabile rossa). | **PORT** (assorbito) |
| `d50e35b` | 09-24 | T3-task #163: pill ZPL/EPL, badge ATTUALE compatto, 2 stampanti per pagina… | functionsFrontend | UX pagina (ordinamento raggiungibili-prime, paginazione). | **PORT** (assorbito) |
| `e963f92` | 09-24 | fix(switch-printer): block selection of unreachable printer | 7 file (di cui 5 solo commenti) | Gate di selezione su `printer_is_reachable` (funzionale in functionsFrontend+env); backup/fiscale/funzioni/index/set = solo commenti. | **PORT** (parte funzionale) |
| `ee15051` | 09-24 | feat(switch-printer): stato CUPS veritiero via parse lpstat -t (#166) | env, functionsFrontend | **Funzionale**: `printer_lpstat_bin/snapshot/parse_stato` (parser puro dei formati `-p/-a/-v/-o`), `printer_lpstat_stato()`, `printer_cups_queue()`; `invia_file_stampa()` usa la coda CUPS. | **PORT** (core F4) |
| `457fe31` | 09-24 | fix(switch-printer): mappa coda CUPS reale GX420t (#166) | diag_166.php, env, functionsFrontend, funzioni, test | `printer_cups_queue()` usato anche nel `cancel` pre-spegnimento; `diag_166.php` diagnostico poi rimosso. | **PORT** (solo `printer_cups_queue`; diag SKIP) |
| `f48db55` | 09-24 | chore(switch-printer): rimuovi scritte stato/CUPS/ping e diag_166.php | diag (del), functionsFrontend | Cleanup intermedio. | **SKIP** |
| `8534f07` | 09-24 | fix(switch-printer): verde solo con presenza USB reale su questa cassa (#167) | env, functionsFrontend, test | **Funzionale**: `printer_stato_locale_ok()`, `printer_usb_presente_da_evidenza()` (pura/testabile), `printer_usb_locale_presente()` (**solo `lpinfo -v`**, nodi /dev scartati come inattendibili; se lpinfo manca → ci si fida della coda = `true`). | **PORT** (parte F4) |
| `096f53d` | 09-24 | fix(switch-printer): runna lp stat -t e poi -p per visualizzare stati… | env, test | **Funzionale**: conferma `lpstat -p` (`printer_lpstat_p_snapshot/p_stato/applica_conferma_p`, fusione pura testabile). | **PORT** (parte F4) |
| `272aee3` | 09-24 | fix(switch-printer): rimuove riga STATO da ogni scheda (#169) | functionsFrontend | Rimozione riga STATO (motivo resta nel messaggio di gate). Stato finale UI. | **PORT** (assorbito: non reintrodurre la riga) |
| `5fe9f0d` | 09-25 | refactor(stampanti): nomi logici reali ZD230, GX420t, TLP2844, LP2844 | env, functionsFrontend, funzioni, set, test | **Funzionale, breaking per i nomi**: `Zebra_Multi→GX420t`, `Zebra_Net→ZD230`, `Zebra_EPL_1/2→TLP2844/LP2844` in gate + messaggi `genera_file_stampa` + registry. | **PORT** (rinomina F4) |
| `91fca24` | 09-25 | fix(switch_printer): GX420t verde se la coda CUPS stampa, USB solo da lpinfo -v | env, test | Raffinamento `printing` = ok + USB solo-lpinfo (finale). | **PORT** (assorbito) |
| `0632388` | 09-25 | Fix printer switch grid | functionsFrontend, style | Griglia 2×2 (`switch-printer-grid/panel/card`, `$rpp` 2→4). Stato finale CSS+markup. | **PORT** (parte F4) |

### Gruppo E — Auth + Festa (F5, F6)

| Commit | Data | Messaggio | File | Proposito reale | Impatto |
|--------|------|-----------|------|-----------------|---------|
| `96e4ba3` | 09-25 | fix(auth): invalida sessione admin al login fallito/bloccato (#173) | functionsFrontend | **1 riga**: `unset($_SESSION['admin']);` a inizio ramo login POST, prima del check throttle. Chiude il caso `err=1` + pannello admin coesistenti (sessione stale). | **PORT** (F5) |
| `8d32a2b` | 09-25 | feat(admin): pulsante MODIFICA FESTA con schermata ?action=info (#174) | functionsFrontend, index | **Funzionale**: bottone admin, route `info` + gate, `mostraModificaInfo()` (parse `set.inc` come testo saltando righe `//`/`#`, rewrite `EVENT_NAME` max 32 + `DURATA_FESTA` ≥1, POST+CSRF). | **PORT** (F6) |
| `6363fbe` | 09-25 | style(admin): centra schermata ?action=info con label sopra input | functionsFrontend | Centratura form (label flex-column). | **PORT** (assorbito in F6) |

### Chore/commenti (SKIP, verifica dal diff)

- `f2bf302` (09-23): cancella 3 `.md` root + snellisce commenti in 7 file — **nessun funzionale**. Nota: cashier conserva ancora `LEGACY_GRUPPO5_ANALISI.md` + `PHP_STRUCTURE_REVIEW.md`; la cancellazione è decisione di repo, non feature — non in scope.
- `686bf17`, `8cec1d0` (2ª parte), `e963f92` (5 file su 7): solo commenti.
- `4f1385a` e `6b796de`: flip `MODALITA_FIERA` 1 poi 0 = **deploy-state fiera, non portare** (verificato: HEAD stagisti = `"0"`; cashier ha peraltro il flag in `storage/cassa_flags.json` via T17).

---

## 4. Feature Inventory

| Feature | Source Commit(s) | Esiste in CashierSystem? | Stato | Source Files | Target Files | Dipendenze |
|---------|------------------|--------------------------|-------|--------------|--------------|------------|
| F1 TAGLIA a richiesta | `8e569f8`, `c38226e`, `0a7c38e` (concetto da `4f1385a`) | No | **Missing** | stagisti `functionsFrontend.inc` (`tagliaVisibile`, footer), `funzioni.inc` (`genera_file_stampa`, `etichetta_continua`), `style.css` (`#taglia-btn/sign`) | `functionsFrontend.inc`, `funzioni.inc` (solo `etichetta_continua`), `src/Cassa/PrintService.php`, `public/style.css` | F4 (nomi `GX420t`, `printer_taglia_permesso`, `CONTINUOUS_LABEL`); route `s` già valida (query `taglia` non richiede voce in `routes/cassa.php`) |
| F2 RIAVVIA/SPEGNI reali | `57d8298`, `6b796de`, `d57a201` (+ CSS `11ba435`) | Bottoni sì, morti (no route/handler → `AZIONE NON VALIDA`) | **Missing** | stagisti `functionsFrontend.inc` (power funcs), `index.php` | `functionsFrontend.inc`, `public/index.php`, `routes/cassa.php`, `public/style.css`, `.gitignore` | F4 (`printer_cups_queue` per `cancel`); `.admin-msg-big` CSS |
| F3 CAMBIA CARTA STAMPA | `67379f1`, `a79ed89`, `5686e34` | Vecchio PRINT RESET `~JA/~JR` | **Missing** | stagisti `functionsFrontend.inc`, `env.inc` (cartaStampa*), `funzioni.inc` (`cartaStampaInviaContenuto`), `set.inc`, `printerCommand/` (10 file) | `functionsFrontend.inc`, `env.inc`, `src/Cassa/PrintService.php`, `set.inc`, `storage/printerCommand/` (nuovo), `.gitignore` | F4 (mappa file per combo, gate); F1 condivide `CONTINUOUS_LABEL` |
| F4 SWITCH PRINTER + rinomina | `45ed795`, `2a7a3ae`, `284a344`, `9cfb30b`, `d50e35b`, `e963f92`, `ee15051`, `457fe31`, `8534f07`, `096f53d`, `272aee3`, `5fe9f0d`, `91fca24`, `0632388` | No (gate fermo a `Zebra_*`) | **Missing** | stagisti `env.inc` (~480 righe), `functionsFrontend.inc` (`mostraSwitchPrinter`, `impostaStampanteSelezionata`), `set.inc`, `funzioni.inc` (`lpr`+messaggi), `style.css` | `env.inc`, `functionsFrontend.inc`, `set.inc`, `src/Cassa/PrintService.php`, `public/index.php`, `routes/cassa.php`, `public/style.css`, `.gitignore` | Nessuna (è la base) — ma cambia i nomi stampante attesi dal deploy |
| F5 Auth #173 | `96e4ba3` | No (manca `unset` pre-verify) | **Missing** | stagisti `functionsFrontend.inc` (1 riga) | `functionsFrontend.inc` (`cassa_azione_login`) | Nessuna (coordinare con throttle T28) |
| F6 MODIFICA FESTA #174 | `8d32a2b`, `6363fbe` | No | **Missing** | stagisti `functionsFrontend.inc` (`mostraModificaInfo`), `index.php` | `functionsFrontend.inc`, `public/index.php`, `routes/cassa.php` | Nessuna (`DURATA_FESTA` esiste già in `set.inc:76`) |
| Comment slimming / md delete (`f2bf302`, `686bf17`, `8cec1d0`, `80dfe5c`, `b3eca1a` doc) | — | Stile doc diverso (scelta consapevole cashier) | **Not applicable / should not be ported** | — | — | — |
| WIP `b392091` (stub `if(action == )` rotto) | — | N/A | **Not applicable** (superato, mai portare) | — | — | — |
| Deploy-state `MODALITA_FIERA` 1↔0 (`4f1385a`, `6b796de`) | — | Meccanismo diverso (T17 storage flags) | **Not applicable** | — | — | — |

---

## 5. Detailed Porting Analysis

### F1 — TAGLIA a richiesta (?taglia=1, solo GX420t + continua)

Cosa fa: aggiunge un bottone giallo **✂️ TAGLIA** nei due footer (modifica + bottoni) che rilancia la stampa con `?action=s&taglia=1`; il comando di taglio viene appeso al file di stampa **solo** se `taglia=1` + `CONTINUOUS_LABEL` + gate `printer_taglia_permesso()` (= `GX420t` + combo continua). EPL: `C` fuori form; ZPL: `^MMC` dopo `^XA`. Il vecchio `C` automatico in coda a `etichetta_continua` è rimosso (altrimenti ogni scontrino continuo taglierebbe da solo).

Come è in stagisti: `tagliaVisibile()` (`functionsFrontend.inc`), append in `genera_file_stampa()` (`funzioni.inc`, legge `$_GET['taglia']` diretto), CSS `#taglia-btn` gialla `#ffd400` / scritta `#2b3d4e`.

Struttura equivalente in cashier: footer = `mostraFooterModifica($cat)` / `mostraFooterBottoni($cat)` (`functionsFrontend.inc:467,544`, prendono `$cat` come parametro, non `global`); builder continuo in `funzioni.inc:743` (ha ancora `P1\nC\n`); spool in `PrintService::generaFileStampa()`.

Trasferire:
- `tagliaVisibile()` verbatim (solo nomi costanti, già uguali).
- Bottone TAGLIA nei due footer, adattando la firma (`$cat` parametro, `onclick` per `mostraFooterBottoni` come gli altri bottoni).
- In `PrintService::generaFileStampa()`: blocco append `^MMC`/`C` (stessa logica, legge `$_GET['taglia']` come in stagisti — coerente col resto del file che usa costanti globali) + messaggi gate con nuovi nomi (`5fe9f0d`).
- In `funzioni.inc` (`etichetta_continua`, builder puro condiviso): `P1\nC\n` → `P1\n`.
- CSS `#taglia-btn/#taglia-sign` in `public/style.css`.

NON copiare: nulla di superato (`~JK`, `Zebra_GX420t`, gate `showStampa` di `b392091` — HEAD stagisti ha `$showStampa` semplice e cashier pure: nessun cambio routing serve, `taglia` è solo query param di action `s` già valida).

Rischi: dimenticare la rimozione del `C` automatico = doppio taglio / taglio a ogni stampa. Verificare EPL vs ZPL sul banco prima della fiera (comandi diversi per linguaggio).

### F2 — RIAVVIA / SPEGNI reali

Cosa fa: due schermate admin (`?action=restart|shutdown`, POST+CSRF+confirm) che (a) svuotano la coda CUPS in DIRETTA (`cancel -a`, best-effort), (b) schedulano `systemctl poweroff/reboot` o `shutdown -h/-r now` via `sudo` con `sleep 2` in background, (c) mostrano riscontro reale al reload successivo via marker `power_attempt.json` vs uptime (riuscito/fallito/attesa, soglia 180s) + diagnostica kiosk.

Stato cashier: i bottoni `RIAVVIA`/`SPEGNI` esistono (`mostraPannelloAdmin`, righe ~690-691) ma **non esiste né la route né la funzione** → click = schermata `AZIONE NON VALIDA`.

Trasferire (tutto in `functionsFrontend.inc`, stile cashier `cassa_log`):
- `svuotaCodaStampaPreSpegnimento()`, `candidatiAlimentazione()`, `escapaComando()`, `scegliComandoAlimentazione()`, `esitoTentativoPower()` (pura — candidarla al primo test PHPUnit del porting), `uptimeMacchina()`, `scriviMarkerPower()`, `mostraRiscontroPower()`, `mostraDiagnosticaPower()`, `schedulaAzioneAlimentazione()`, `mostraPower()`, `mostraRestart()`, `mostraShutdown()`.
- Route in `public/index.php` (`$showRestart/$showShutdown` + gate admin + rami vista con `mostraNavCategorie($mysqli,$cat)` come gli altri) + voci in `routes/cassa.php`.
- `power_attempt.json` → `salsiccia_storage_path('power_attempt.json')` + `.gitignore`; `error_log` → `cassa_log`.
- Classe `.admin-msg-big` in `public/style.css`.

NON copiare il bug: `mostraDiagnosticaPower()` usa `$mode` indefinito (stagisti riga ~1452) → aggiungere parametro `$mode` e passare dal chiamante `mostraPower()`.

Prerequisiti kiosk (non codice, da documentare nel messaggio della schermata come già fa stagisti): utente web con NOPASSWD sui comandi esatti, systemd presente. Su Windows/dev le exec falliscono → messaggio rosso `COMANDO NON AVVIATO`, nessun crash (verificato: tutti i rami `false` gestiti).

### F3 — CAMBIA CARTA STAMPA (toggle + setup stampante)

Cosa fa: `?action=print_reset` diventa toggle continua↔singoli: al cambio, la stampante riceve via stesso trasporto delle stampe (lpr/FTP) il file di setup corrispondente da `printerCommand/` (`cmd_[STAMPANTE]_[SINGOLI|CONTINUA]_[EPL|ZPL]`), normalizzato (EPL `q464/q832`+`Q`, ZPL `^PW464/^PW832`); solo dopo invio riuscito scrive `carta_stampa.mode`, letto al boot con precedenza env > file > gate. Setup mancante/invalido/invio fallito = errore esplicito, modalità invariata.

Stato cashier: `mostraPrintReset()` vecchio (`~JA/~JR`, `N`, scrive LABELS_FILE) + `CONTINUOUS_LABEL` solo env>gate.

Trasferire:
- `env.inc`: `cartaStampaFilePerStampante()`, `cartaStampaNormalizzaContenuto()`, `cartaStampaLeggiSetup()` verbatim (tiene già `$baseDir` iniettabile).
- Invio setup: `cartaStampaInviaContenuto()` da `funzioni.inc` stagisti → metodo `PrintService` (usa `printer_cups_queue()`, `cassa_log`, `basename(LABELS_FILE)` convenzione T16; FTP anonimo come `ftpPut()` esistente).
- `impostaCartaStampa()` → path storage (`salsiccia_storage_path('carta_stampa.mode')`); `set.inc`: ramo lettura file con precedenza env > `storage/carta_stampa.mode` > gate (mantenendo die-500 cashier per IP).
- `mostraPrintReset()` riscritta sul pattern stagisti (POST+CSRF+confirm, `.admin-msg-big`); label bottone admin con modo corrente come stagisti.
- `printerCommand/` 10 file finali (`5686e34`) → `storage/printerCommand/` (fuori docroot `public/`), passati come `$baseDir`; `.gitignore` per `storage/carta_stampa.mode`.
- `test_carta_stampa.php` NON copiare: riferimento per 1-2 casi PHPUnit in `tests/` (normalizzazione EPL/ZPL + mappa nomi).

NON copiare: i 19 nomi intermedi di `74a7a09`, i file rimossi in `f213d06`.

Rischi: path `__DIR__.'/printerCommand'` copiato verbatim punterebbe in `src/` o `public/` a seconda del file — usare sempre `$baseDir` esplicito. Il toggle è ininfluente con env impostata (documentato nel codice, mantenere il commento).

### F4 — Registry + selezione + pagina CAMBIA STAMPANTE + rinomina nomi

Cosa fa (stato finale HEAD): 4 stampanti note (`ZD230` RETE/ZPL, `GX420t` DIRETTA dual, `TLP2844`/`LP2844` DIRETTA/EPL); selezione persistita in JSON validata contro gate e letta a boot; pagina admin con card 2×2, pallino verde/rosso da stato CUPS reale (`lpstat -t` parsato + conferma `lpstat -p` + presenza USB da `lpinfo -v` per DIRETTA, ping TCP per RETE), raggiungibili prime, blocco selezione se irraggiungibile (tranne RETE che resta ping-only); `lpr` usa la coda CUPS reale.

Trasferire:
- `env.inc`: tutto il blocco `printer_*` (~480 righe) verbatim salvo `cassa_log` dove c'è `error_log` (2 punti: `lpstat -t/-p fallito`, setup illeggibile in `cartaStampaLeggiSetup`).
- `set.inc`: lettura selezione a boot + validazione IP env con `FILTER_VALIDATE_IP` (migliora il fail-closed cashier senza romperlo: env invalida → fallback selezione/salvataggio invece di die? **Decisione**: mantenere die-500 cashier per env vuota; per env malformata adottare il controllo stagisti. Da esplicitare nell'implementazione).
- `impostaStampanteSelezionata()` + `mostraSwitchPrinter()` (stato finale con grid, `$rpp=4`, senza riga STATO per `272aee3`) + bottone admin + route + voce `routes/cassa.php`.
- Rinomina `Zebra_Multi→GX420t`, `Zebra_Net→ZD230`, `Zebra_EPL_1/2→TLP2844/LP2844` in gate + messaggi `PrintService::generaFileStampa()` + default `PRINTER_NAME` (`Zebra_Net`→`ZD230`, stessa combo RETE/ZPL) + `.env.example` riga 22.
- `printer_cups_queue()` in `PrintService::inviaFileStampa()` e nel `cancel` di F2.
- CSS `.switch-printer-*` in `public/style.css`; `.gitignore` per lo state-file.
- `test_printer_gate.php` NON copiare: riferimento per casi PHPUnit (parser `lpstat` puro + `printer_stato_locale_ok` + evidenza USB sono già scritte testabili).

NON copiare: `diag_166.php` (rimosso upstream), riga STATO per-scheda (rimossa in `272aee3`), paginazione a 2 (finale 4), `FINDINGS-switch_printer.md`.

Rischi: **deploy** — il nome di coda CUPS reale deve coincidere (`printer_cups_queue()` = identità); se sul kiosk le code si chiamano ancora `Zebra_*`, la rinomina rompe `lpr` finché CUPS non è riallineato. Verificare sul kiosk prima del merge. Su Windows/dev senza CUPS: tutto rosso "non verificabile", selezione bloccata — comportamento corretto ma da sapere in collaudo.

### F5 — Auth #173 (1 riga)

`unset($_SESSION['admin']);` a inizio ramo login POST in `cassa_azione_login()` (`functionsFrontend.inc:124-136`, prima del check throttle), così un tentativo (fallito o throttled) invalida sempre la sessione admin precedente. Coordinare con T28: il throttle file-based (`salsiccia_login_throttled('admin')`) sopravvive al reset sessione per design — nessun conflitto.

### F6 — MODIFICA FESTA (?action=info)

`mostraModificaInfo()` + bottone admin + route `info` + gate + voce `routes/cassa.php`. Il parse testuale di `set.inc` funziona sul `set.inc` cashier senza modifiche (formati `define("EVENT_NAME",  "...")` e `define("DURATA_FESTA", "1");` identici). Path: `__DIR__.'/set.inc'` va adattato alla posizione reale del file rispetto a `functionsFrontend.inc` (stessa dir in entrambi i repo — verificare in implementazione). Avvertenza cashier-specifica (T17): `set.inc` è destinato al freeze in `config/cassa.php` — il self-rewrite va contro quella direzione ma è l'unico modo fedele allo stagisti; alternativa (consigliata se il freeze è vicino): scrivere i due valori nel flag store invece che nel sorgente. **Decisione esplicita richiesta all'implementazione**; default = replica fedele.

---

## 6. Cross-Repository Mapping

| Source (stagisti) | Target (cashierSystem) | Perché questo punto |
|---|---|---|
| `index.php` (`$showRestart/Shutdown/SwitchPrinter/Info`, gate admin-array, rami vista) | `public/index.php` (stesse variabili/rami, con `$mysqli,$cat` espliciti) + `routes/cassa.php` (`restart/shutdown/switch_printer/info => null`) | Front controller spostato in `public/` + validazione tabellare T23: senza la voce in tabella, le nuove action cadono in `AZIONE NON VALIDA` |
| `functionsFrontend.inc` `gestisciAzioni()` ramo login | `functionsFrontend.inc` `cassa_azione_login()` | Stesso ramo, estratto in handler T23 |
| `functionsFrontend.inc` `mostraPrintReset/mostraPower/mostraRestart/mostraShutdown/mostraSwitchPrinter/mostraModificaInfo/impostaCartaStampa/impostaStampanteSelezionata/tagliaVisibile` | `functionsFrontend.inc` (stesse funzioni; footer con `$cat` parametro) | Casa delle `mostra*` invariata in cashier |
| `funzioni.inc` `genera_file_stampa` (gate-messaggi + append taglia) | `src/Cassa/PrintService.php::generaFileStampa()` | Spostata da T32; i messaggi `Zebra_*` da rinominare sono qui (`:35-41`) |
| `funzioni.inc` `invia_file_stampa` (`lpr -P PRINTER_NAME`) | `PrintService::inviaFileStampa()` (`:162-168`) | Usare `printer_cups_queue(PRINTER_NAME)` |
| `funzioni.inc` `cartaStampaInviaContenuto` | Nuovo metodo `PrintService` (stesso file del trasporto) | `funzioni.inc` cashier tiene solo builder puri (T32) |
| `funzioni.inc` `etichetta_continua` (`P1\nC\n`) | `funzioni.inc:743` (`P1\nC\n` identico) | Builder rimasto in `funzioni.inc` in entrambi |
| `funzioni.inc` `ftpPut` | `PrintService::ftpPut()` (`:173-202`) | Pattern per il ramo RETE di `cartaStampaInviaContenuto` (anonimo, timeout 5s, `basename`) |
| `env.inc` blocco `printer_*` + `cartaStampa*` | `env.inc` (dopo `printer_is_continuous`, ~riga 155) | Stessa casa del gate; `salsiccia_storage_path()` disponibile sopra (`:34-43`) |
| `set.inc` (selezione boot + `carta_stampa.mode`) | `set.inc` (`:46-71`, blocco printer) | Stessa casa; mantenere die-500 cashier |
| `set.inc` `EVENT_NAME`/`DURATA_FESTA` (scritti da F6) | `set.inc:21,76` | Formati riga identici, parse testuale riusabile |
| `style.css` (`#taglia-*`, `.admin-msg-big`, `.switch-printer-*`) | `public/style.css` (sezione admin ~riga 692+) | CSS spostato in `public/`; classi admin esistenti già lì |
| `.gitignore` (`carta_stampa.mode`, `stampante_selezione.json`, `power_attempt.json`) | `.gitignore` (`storage/carta_stampa.mode`, `storage/stampante_selezione.json`, `storage/power_attempt.json`) | Docroot cashier = `public/`, runtime in `storage/` (convenzione T16, già ignorata a pattern) |
| `printerCommand/` (10 file `5686e34`) | `storage/printerCommand/` (nuovo, via `$baseDir`) | Fuori docroot; `cartaStampaLeggiSetup()` accetta già base alternativa |
| `test_printer_gate.php`, `test_carta_stampa.php` | `tests/` (nuovi casi PHPUnit, non copia) | Harness T27; funzioni pure già isolate per il test |
| `error_log()` nei blocchi portati | `cassa_log('error'|'warning', …)` | Facade T24; firme in `funzioni.inc:8-27` / `src/Support/` |

---

## 7. Feature Dependencies and Porting Order

```
F4-registry (gate + nomi + printer_cups_queue + storage state-file)
 ├─→ F1 TAGLIA (usa printer_taglia_permesso + nuovi nomi + CONTINUOUS_LABEL)
 ├─→ F3 CARTA (usa gate + mappa combo + invio lpr/FTP + carta_stampa.mode)
 └─→ F2 POWER (usa printer_cups_queue nel cancel + .admin-msg-big)
F5 AUTH ─────────────── indipendente, in qualsiasi momento (1 riga)
F6 FESTA ────────────── indipendente (route + mostra* + CSS già da F2)
CSS .admin-msg-big ──── con F2 (prima schermata che lo usa)
```

Ordine consigliato: **F4 → F1 → F3 → F2 → F5 → F6** (F5/F6 anticipabili liberamente). F4 è il collo di bottiglia: senza i nuovi nomi, F1/F3 non compilano il gate corretto. Verifiche kiosk (code CUPS reali, `lpstat`/`lpinfo`, sudoers) dopo F4 e dopo F2.

---

## 8. Missing Pieces Checklist

- [ ] `env.inc`: blocco `printer_*` F4 (gate rinominato, registry, selezione, CUPS `lpstat -t/-p`, USB `lpinfo -v`, ping, `printer_cups_queue`, `printer_taglia_permesso`) + `cartaStampa{FilePerStampante,NormalizzaContenuto,LeggiSetup}` (F3), con `cassa_log`
- [ ] `set.inc`: lettura `stampante_selezione.json` da storage + default `PRINTER_NAME ZD230`; precedenza `CONTINUOUS_LABEL` env > `storage/carta_stampa.mode` > gate; validazione IP env
- [ ] `src/Cassa/PrintService.php`: messaggi gate rinominati; append `^MMC`/`C` su `?taglia=1`; `inviaFileStampa()` via `printer_cups_queue()`; nuovo metodo invio setup carta (F3)
- [ ] `funzioni.inc`: `etichetta_continua` senza `C` automatico
- [ ] `functionsFrontend.inc`: `tagliaVisibile()` + bottoni TAGLIA nei due footer; `mostraPrintReset()` riscritta + label bottone modo corrente; `mostraPower/Restart/Shutdown` (+ diagnostica con `$mode` fixato); `mostraSwitchPrinter()`; `mostraModificaInfo()`; `impostaCartaStampa/impostaStampanteSelezionata()` su storage; `unset($_SESSION['admin'])` in `cassa_azione_login()`; bottoni `CAMBIA STAMPANTE` + `MODIFICA FESTA` nel pannello
- [ ] `public/index.php`: route `restart/shutdown/switch_printer/info` + gate admin + rami vista + esclusioni tinta
- [ ] `routes/cassa.php`: 4 voci schermata (`=> null`)
- [ ] `public/style.css`: `#taglia-btn/sign`, `.admin-msg-big`, `.switch-printer-{panel,grid,card}` + varianti
- [ ] `.gitignore`: `storage/carta_stampa.mode`, `storage/stampante_selezione.json`, `storage/power_attempt.json`
- [ ] `storage/printerCommand/`: 10 file `cmd_*` finali (`5686e34`)
- [ ] `.env.example`: riga 22 (nomi reali, non più solo RETE+ZPL)
- [ ] `tests/`: casi PHPUnit per `esitoTentativoPower`, parser `lpstat`, `printer_stato_locale_ok`, evidenza USB, normalizzazione carta, mappa file (da script stagisti come riferimento)
- [ ] Verifiche kiosk: nomi code CUPS = nomi logici; `lpstat`/`lpinfo` presenti; NOPASSWD sudoers; prova taglio EPL+ZPL; prova toggle carta; prova restart su macchina di test (mai in fiera)

---

## 9. Final Porting Map

| # | Feature | Source commit(s) | Target files | Required changes | Dependencies | Order | Verification |
|---|---------|------------------|--------------|------------------|--------------|-------|--------------|
| 1 | F4 registry+rinomina+CUPS | `45ed795`, `ee15051`, `457fe31`, `8534f07`, `096f53d`, `5fe9f0d`, `91fca24`, `284a344`, `9cfb30b`, `d50e35b`, `e963f92`, `272aee3`, `0632388` | `env.inc`, `set.inc`, `functionsFrontend.inc`, `public/index.php`, `routes/cassa.php`, `public/style.css`, `.gitignore`, `PrintService.php` (coda+messaggi), `.env.example` | Gate `ZD230/GX420t/TLP2844/LP2844`; selezione JSON in storage; pagina switch; `lpr` via coda CUPS | Nessuna | 1º | PHPUnit parser/gate; su kiosk: `lpstat -t`, code = nomi logici, pallini corretti |
| 2 | F1 TAGLIA | `8e569f8`, `c38226e`, `0a7c38e` | `functionsFrontend.inc`, `funzioni.inc`, `PrintService.php`, `public/style.css` | Bottone + gate + append `^MMC`/`C` + rimozione `C` auto | #1 | 2º | Stampa con/senza `taglia=1` su GX420t ZPL ed EPL; mai taglio in singoli |
| 3 | F3 CARTA STAMPA | `67379f1`, `a79ed89`, `5686e34` | `functionsFrontend.inc`, `env.inc`, `PrintService.php`, `set.inc`, `storage/printerCommand/` (10 file), `.gitignore` | Toggle + setup via lpr/FTP + mode-file in storage | #1 | 3º | Toggle con stampante raggiungibile/non + file mancante; PHPUnit normalizzazione |
| 4 | F2 POWER | `57d8298`, `6b796de`, `d57a201`, `11ba435` (CSS) | `functionsFrontend.inc`, `public/index.php`, `routes/cassa.php`, `public/style.css`, `.gitignore` | Schermate + preflight + marker/uptime + `.admin-msg-big`; **fix `$mode` diagnostica** | #1 (cancel) | 4º | PHPUnit `esitoTentativoPower`; su macchina test: schedulazione + riscontro reali |
| 5 | F5 AUTH | `96e4ba3` | `functionsFrontend.inc` (`cassa_azione_login`) | `unset($_SESSION['admin'])` pre-throttle | Nessuna | libero | Login fallito con sessione admin preesistente → deautenticato |
| 6 | F6 FESTA | `8d32a2b`, `6363fbe` | `functionsFrontend.inc`, `public/index.php`, `routes/cassa.php` | Bottone + `?action=info` + rewrite `set.inc` (decidere freeze T17) | Nessuna | libero | Salvataggio EVENT_NAME 32ch + DURATA≥1; CSRF; no-admin redirect |

Incertezze residue (da sciogliere in implementazione, non bloccanti per la pianificazione): nessuna sul *cosa* portare — ogni riga sopra è tracciata a un diff. Le due decisioni esplicite sono marcate in §5 (F4 env-IP-malformata, F6 freeze-T17). File di test stagisti non ispezionati riga per riga (solo stat + origine): da usare come riferimento, non come spec.

...[truncated 20862 chars]