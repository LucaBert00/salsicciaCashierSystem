# INC_REVIEW.md — root-level `.inc` files: findings, target structure, migration plan

> Status: plan only. No production files modified.
> Date: 2026-09-21. Measured sizes: `set.inc` 117 lines, `env.inc` 205,
> `funzioni.inc` 892, `functionsFrontend.inc` 1027.
> Note: `PHP_STRUCTURE_REVIEW.md` is read-only per `routes/cassa.php:8`
> (Mappa #10) and was NOT modified; this file is its companion for the
> `.inc` question only.

---

## 1. Findings per file

### `set.inc` (117) — acceptable, keep as-is

Single responsibility: frozen deploy-time constants (printer combo, layout
geometry, event/feast, fail-closed secret reads). Small, coherent, already
read-only (runtime flag writes moved to `storage/cassa_flags.json`). Only wart
is 6 lines of legacy IP→`ID_CASSA` sniffing (`$remoteIP`, lines 101–106),
which is already superseded by session selection in `cassaCorrente()` and kept
as fallback. Splitting 117 lines buys nothing.

### `env.inc` (205) — acceptable, keep as-is

Five small groups (log facade, `.env` loader, storage-path helpers,
session+login-throttle, printer gate, fiera flags) under one theme: process
bootstrap / runtime support. All functions are short and pure-ish. Critically,
it is the lowest-level leaf: required directly by `dbConnect.php`,
`funzioni.inc`, `functionsFrontend.inc`, all four `public/reserved/*.php`
pages, three `src/` classes, and `tests/Unit/SessionThrottleTest.php`.
Splitting it into 5 tiny `src/Support/*` files would churn ~10 require sites
for zero readability gain.

### `funzioni.inc` (892) — problematic, split recommended

Six unrelated responsibilities in one file:

1. CSRF helpers (`csrf_*`, lines 13–29) — security/HTTP
2. DB layer (`mysql_query_safe`/`db_select`/`db_exec`, 37–82) — data access,
   also required back by `src/Cassa/OrderService.php` and
   `src/Catalog/CatalogRepo.php`
3. Money math (`calcolaTotali`, 90–172)
4. Label builders (`testo_biglietti`, `stampaMenu`, `generaCardDegustazione`,
   `etichetta_continua`, `get_product_label`, ~500 lines combined) — includes
   dead-ish `stampaMenu` with hardcoded 2018 menu items
5. HTML widgets (`printOre`, `stampa_contatori`)
6. Counter queries (`conta_prodotto`, `contatori_totali`)
7. Side effect at include: `ini_set`/`error_reporting` (lines 7–9) hitting
   every consumer including tests.

Size + unrelatedness + fan-in (required by frontend, backoffice, `src/`,
tests) = the split materially improves readability and breaks the circular
`src/` ↔ root `.inc` requires.

### `functionsFrontend.inc` (1027) — problematic, split recommended

Three layers fused: bootstrap with side effects at include time (requires +
`salsiccia_session_start()` + `$cat` resolution + `gestisciAzioni()` dispatch
executing on lines 58–70), 11 HTTP action handlers (`cassa_azione_*`, already
thin over `OrderService`), and ~20 `mostra*` echo views. Partial successors
already exist (`OrderService`, `CatalogRepo`, `CatalogView`, `PrintService`,
`routes/cassa.php`), so the file is mid-migration. Include-time dispatch is
the sharpest edge: merely requiring the file mutates orders, which is why it
can never be unit-tested.

## 2. Reasoning (against over-engineering)

- The old `PHP_STRUCTURE_REVIEW.md` (§2–§5) describes a repo that no longer
  exists: Composer/PSR-4, `public/` docroot, `src/` services, `config/`,
  `routes/`, `tests/` all landed since. Judge against the current
  architecture, not that snapshot.
- Docroot is `public/` (T16) with root `.htaccess` as second belt. Root `.inc`
  files are outside the docroot — root is therefore the correct home for
  non-public includes, and staying in root is justified. Moving them to
  `public/` would be a regression; moving to `src/` only makes sense
  per-function as classes.
- `.inc` vs `.php`: outside the docroot the extension is
  disclosure-irrelevant (nothing in root is web-served; `.htaccess` covers
  root-docroot deploys). `.inc` usefully signals "fragment, require-me". Keep
  `.inc` names until each file is empty; renaming now breaks ~15 require sites
  for convention cosmetics. All new code goes in `src/*.php` per the
  established PSR-4 pattern.
- `phpcs.xml` already grandfather-lists all four files — style normalization
  is explicitly deferred; the plan must not smuggle in a reformat.

## 3. Recommended target structure

```text
set.inc                     ← KEEP (frozen defaults; §4 step 0 trims $remoteIP leak)
env.inc                     ← KEEP (bootstrap leaf; only grows a deprecation header)
src/Support/Db.php          ← FROM funzioni.inc db_select/db_exec/mysql_query_safe + dbConnect.php
src/Support/Csrf.php        ← FROM funzioni.inc csrf_token/csrf_ok/csrf_field
src/Cassa/LabelBuilder.php  ← FROM funzioni.inc etichetta_continua/get_product_label/testo_biglietti
                              (+ generaCardDegustazione; stampaMenu deleted, see below)
src/Cassa/OrderService.php  ← ADD calcolaTotali() as method (from funzioni.inc:90)
src/Stats/StatsData.php     ← ADD contatori_totali()/conta_prodotto() (from funzioni.inc:459-518)
src/Cassa/CassaActions.php  ← FROM functionsFrontend.inc cassa_azione_* + gestisciAzioni()
src/Cassa/CassaView.php     ← FROM functionsFrontend.inc mostra* (CatalogView pattern)
public/index.php            ← absorbs the include-time bootstrap (session/$cat/dispatch call)
funzioni.inc / functionsFrontend.inc ← thin require-shims, deleted last
```

`stampaMenu()` (~130 lines, hardcoded EPL menu from a past event, no callers
found in `public/` flow) is a deletion candidate after a caller grep confirms;
`printOre()`/`stampa_contatori()` go to the view with their callers.

## 4. Concrete migration plan (behavior-preserving, no big-bang)

1. Step 0 — confirm dead code (read-only): grep callers of `stampaMenu`,
   `printA`, `printOre`, `stampa_contatori` across `public/`, `reserved/`,
   `src/`, `tests/`. If uncalled, mark for deletion instead of migration.
2. Step 1 — pin behavior with tests: extend `tests/Unit/PureBuildersTest.php`
   (already covers `etichetta_continua`) with `testo_biglietti` +
   `get_product_label` cases before moving them. No production edits.
3. Step 2 — extract DB layer: new `src/Support/Db.php` (static select/exec
   wrappers or namespaced functions matching current signatures), keep
   `db_select()`/`db_exec()` in `funzioni.inc` as one-line delegates. Update
   `src/` classes to the new home first (4 require sites), pages later.
4. Step 3 — extract CSRF: new `src/Support/Csrf.php`, same delegate-shim
   treatment. Consumers: `functionsFrontend.inc` + backoffice pages.
5. Step 4 — extract label builders: new `src/Cassa/LabelBuilder.php` (pure
   static methods; `PrintService` already documents this as the intended home
   in its header comment). `funzioni.inc` keeps delegates until
   `tests/bootstrap.php` switches to the class.
6. Step 5 — move `calcolaTotali` into `OrderService` as a method (it already
   takes `$in_txn` for the T08 contract); keep the procedural wrapper for
   `mq`/`mr` paths during transition.
7. Step 6 — split `functionsFrontend.inc`: move the include-time side effects
   (lines 58–70) into `public/index.php` explicitly; move `cassa_azione_*` +
   `gestisciAzioni()` to `src/Cassa/CassaActions.php` keeping the
   `routes/cassa.php` table unchanged; move `mostra*` to
   `src/Cassa/CassaView.php` following the `CatalogView` static-renderer
   precedent.
8. Step 7 — delete: when both `.inc` files are delegate-only, drop them and
   fix the ~15 require sites in one commit (`public/index.php`,
   `public/reserved/*`, `src/*`, `tests/bootstrap.php`, `phpunit.xml`
   `<source>` entry). Remove their `phpcs.xml` grandfather entries at the
   same time.
9. Explicitly out of scope: renaming `set.inc`/`env.inc`, reformatting legacy
   style, PSR-3 logger, moving files to `includes/`. Revisit only via the T31
   headers' 2026-12-31 review date.

---

## 5. Finished-structure visualization

Legend: KEEP untouched · MOVE relocated logic · NEW created ·
SHIM thin delegate, deleted last · DEL removed.

```text
salsicciaCashierSystem/
├── set.inc                          ← KEEP (frozen deploy defaults, 117 righe; solo trim $remoteIP)
├── env.inc                          ← KEEP (bootstrap leaf, 205 righe; + header "non estendere")
├── dbConnect.php                    ← SHIM → src/Support/Db.php (fail-closed invariato)
├── funzioni.inc                     ← SHIM → DEL (svuotato per-funzione, deleghe da 1 riga)
├── functionsFrontend.inc            ← SHIM → DEL (bootstrap esplicitato, resto in src/)
├── config/cassa.php                 ← KEEP (default inerti; nessun cambio)
├── routes/cassa.php                 ← KEEP (tabella invariata; handler risolti in CassaActions)
├── public/
│   ├── index.php                    ← MOVE-IN bootstrap esplicito (session/$cat/dispatch,
│   │                                  oggi side-effect nascosto in functionsFrontend.inc:58-70)
│   └── reserved/*.php               ← KEEP (require aggiornati ai nuovi src/)
├── src/
│   ├── Support/
│   │   ├── ErrorHandler.php         ← KEEP
│   │   ├── Db.php                   ← NEW ← funzioni.inc db_select/db_exec/mysql_query_safe
│   │   └── Csrf.php                 ← NEW ← funzioni.inc csrf_token/csrf_ok/csrf_field
│   ├── Cassa/
│   │   ├── OrderService.php         ← KEEP + metodo calcolaTotali() ← funzioni.inc:90
│   │   ├── PrintService.php         ← KEEP
│   │   ├── LabelBuilder.php         ← NEW ← etichetta_continua/get_product_label/
│   │   │                                     testo_biglietti/generaCardDegustazione
│   │   ├── CassaActions.php         ← NEW ← cassa_azione_* + gestisciAzioni()
│   │   ├── CassaView.php            ← NEW ← mostra* (~20, pattern CatalogView)
│   │   ├── OrderType.php / PayMethod.php ← KEEP
│   ├── Catalog/                     ← KEEP (pattern di riferimento per gli split)
│   ├── Stats/StatsData.php          ← KEEP + contatori_totali()/conta_prodotto()
│   ├── Fiscale/ / Backup/ / Backoffice/ ← KEEP
├── tests/Unit/PureBuildersTest.php  ← esteso PRIMA degli split (testo_biglietti, get_product_label)
├── storage/ / database/ / vendor/   ← KEEP
└── phpcs.xml                        ← grandfather entries rimosse a file svuotati
```

Include-time lifecycle, before → after:

```text
PRIMA: public/index.php ──require──▶ functionsFrontend.inc ─┬─▶ set.inc ─▶ env.inc
       (side-effect: session+$cat+dispatch  ────────────────┤
        eseguiti al require, righe 58-70)                   ├─▶ dbConnect.php ─▶ env.inc
                                                            ├─▶ funzioni.inc ─▶ env.inc
                                                            └─▶ src/* ──require-back──▶ funzioni.inc/env.inc
AFTER: public/index.php ──require──▶ env.inc ─▶ set.inc ─▶ src/Support/Db.php ...
                      ──explicit──▶ session start, $cat, CassaActions::dispatch()
       src/*: solo require verso src/Support/*, mai verso root .inc (ciclo spezzato)
       tests/bootstrap.php: vendor/autoload + src/*, niente .inc
```
