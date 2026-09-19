# STRUCTURE-ONLY PHP Review — SalsicciaStagisti

> Read-only review. No file was modified. Framework indeterminate from repo (no `composer.json` found) → reviewed as **framework-agnostic procedural PHP**. PHP runtime version **unverified** (`php` binary not present in this shell, no version constraint anywhere in repo).

---

## 1. Executive summary

1. This is a **touch-kiosk cash register ("cassa")** for festival food stands: cashier UI + label-printer output + fiscal receipt via network cash register + backoffice CRUD/stats/backup.
2. Structurally it is **flat legacy PHP**: ~13 PHP files in web root + `reserved/`, `require_once` chains, zero namespaces, zero Composer, zero tests, zero migrations.
3. **Biggest strength:** the *newer* modules (`fiscale.inc`, `backup.inc`, `stat_dati.inc`, backoffice CRUD) are carefully written — prepared statements, pure/testable builders, shared data layer, non-blocking fiscal fallback queue.
4. **Biggest risk:** there is **no web-root separation** — `.inc` source, `.env` secrets, and fiscal queue logs all live in the docroot, protected only by `.htaccess` (Apache-only, one misconfiguration away from source/secret disclosure).
5. Second risk (tie): **cashier order flows build SQL by string interpolation** (`mysql_query_safe` = raw `mysqli_query`) with no transactions — one missed `(int)` cast or one mid-flow failure = injection or partial order.
6. Auth is **shared plaintext secrets** (admin kiosk code; MySQL `PASSWORD()` double-SHA1 for backoffice) — no `password_hash`/Argon2 anywhere.
7. State-changing cashier actions run over **GET with no CSRF token** (by kiosk design, but `action=ra&id=` reactivates *any* order id with no ownership check).
8. `set.inc` is **rewritten at runtime** by the app itself (feature-flag toggle via `file_put_contents` on source) — race-prone and opcache-hostile.
9. Good news: output escaping (`htmlspecialchars`), CSRF tokens on admin/backoffice POST, and per-session rate limiting show the team *knows* the right patterns — they just aren't applied uniformly.
10. Fastest structural win: introduce `public/` docroot + Composer PSR-4 `src/` and move includes/env/logs out of web reach; everything else phases behind that.

---

## 2. Structure map

### 2.1 PHP-relevant tree (one-line role per dir/file)

```
.                          ← IS the docroot (no public/ split) — served as-is by Apache/XAMPP
├── index.php              ← cashier front controller: ?action= dispatch → echo schermate
├── set.inc                ← deploy constants (printer, layout, PWD fallback); INCLUDED as code
├── env.inc                ← .env loader + printer-gate truth table (pure functions)
├── dbConnect.php          ← mysqli_connect from SALSICCIA_DB_* env w/ hardcoded fallback
├── funzioni.inc           ← 967 vv legacy lib: CSRF helpers, mysql_query_safe, label builders, ftp/lpr send
├── functionsFrontend.inc  ← 1139 vv cashier controller+view+model: gestisciAzioni + mostra* echo functions
├── fiscale.inc            ← fiscal receipt module (strict_types, pure builder/parser/transport/queue)
├── backup.inc             ← end-of-day DB dump module (strict_types, mysqldump→PHP fallback)
├── reserved/              ← backoffice area (separate session auth: reserved_auth)
│   ├── login.php          ← backoffice login (prepared, session rate-limit)
│   ├── visualizza.php     ← 1468 vv 5-tab CRUD (config-whitelist + prepared + inline HTML/JS)
│   ├── statistiche.php    ← stats page (reads via shared stat_dati.inc, inline SVG charts)
│   ├── stat_dati.inc      ← shared stats data layer (prepared, reused by page + PDF export)
│   ├── stat_pdf.php       ← on-demand PDF export (CLI guard, no disk writes)
│   ├── backup.php         ← dump trigger page (POST+CSRF, writes outside docroot)
│   └── fpdf/              ← FPDF library VENDORED BY COPY (no Composer, version unverified)
├── resources/             ← printer manuals (EPL2/ZPL PDFs+MD) + linkLabel — docs only, no PHP
├── asset/                 ← banknote/coin PNGs + background — static, referenced by PHP echo
├── .env                   ← secrets (EXISTS on disk, untracked by git — good; but IN docroot — bad)
├── .htaccess              ← only barrier between docroot secrets/sources and HTTP
└── (ABSENT)               ← no composer.json, no src/, no config/, no routes/, no database/migrations/, no tests/
```

Evidence: root listing (20 entries, no `composer.json`/`src`/`tests`/`config`/`database` dirs); `git ls-files` shows zero PHP-infra files beyond the flat list; `*.php` glob = 10 files total.

### 2.2 Request lifecycle traces (2 flows, end to end)

**Flow A — cashier sale (`index.php`):**

```
GET index.php?cat=19&action=a&id=7
 └─ index.php:1            require_once functionsFrontend.inc
     └─ functionsFrontend.inc:3-6   require set.inc → env.inc(.env) → dbConnect.php(mysqli) → funzioni.inc → fiscale.inc
     ├─ functionsFrontend.inc:9-10  session_start (admin flag)
     ├─ functionsFrontend.inc:30    $cat = (int)$_GET['cat']
     ├─ functionsFrontend.inc:40    gestisciAzioni()  ← runs BEFORE any HTML
     │    ├─ :192-244  action=a: SELECT ordini … → INSERT/UPDATE righe_ordini (INTERPOLATED SQL) → calcolaTotali()
     │    └─ funzioni.inc:51-90 calcolaTotali(): 3× SELECT/UPDATE round-trips, NO transaction
     ├─ index.php:3-28   $action routing → $showStampa/$showModifica/…
     └─ index.php:40-101 echo HTML: mostraNavCategorie() + mostraTabellaProdotti() + mostraBoxRiepilogo() + mostraListaProdotti()
          (each re-queries with global $mysqli; SQL+HTML in same function)
```

**Flow B — print + fiscal receipt (`?action=s`):**

```
GET index.php?action=s
 └─ mostraSchermataStampa()  functionsFrontend.inc:1030
     ├─ :1071-1076  SELECT righe… (interpolated, $id_cassa int-cast)
     ├─ :1087  genera_file_stampa()  funzioni.inc:646
     │    ├─ :658  printer_gate_allowed() (env.inc:21 — single truth table, GOOD)
     │    ├─ :686-697 CONTINUOUS path → etichetta_continua() pure builder (GOOD, testable)
     │    └─ :913-930 invia_file_stampa() → lpr|ftpPut, error_log on failure
     ├─ :1095-1100  send failed → order stays aperto, JS-redirect to stampa_err branch (GOOD: never closes unsent)
     ├─ :1105  send ok → UPDATE ordini SET chiuso=1 (single UPDATE, no transaction w/ print — accepted kiosk tradeoff)
     └─ second hit ?action=s&stampato=N → :1124-1136  metodo=whitelist → fiscale_emetti_scontrino()  fiscale.inc:212
          ├─ :221 PREPARED GROUP BY iva query (GOOD — only prepared stmt in fiscal path)
          ├─ :50-67  fiscale_build_xml() pure builder (GOOD)
          ├─ :89-117 fiscale_trasmetti() isolated transport, short timeouts
          ├─ :139-145 fallback → JSON line appended to queue file, sale CONTINUES (GOOD — Fallback/Coda per CONTEXT.md)
          └─ :219-220 idempotency: fiscale_gia_emesso() blocks double-emit
```

**Flow C (backoffice, summarized):** `reserved/login.php:16-20` prepared auth → `$_SESSION['reserved_auth']` → `visualizza.php:35-76` tab-config whitelist → `visualizza.php:814-832` prepared COUNT+SELECT with `LIMIT (int)offset,(int)rpp` → inline form/list HTML with `csrf_field()` on every POST delete/save (`visualizza.php:260-387`, `:469-743`).

---

## 3. What is GOOD — keep it (top 5)

**G1. Env-based secrets, `.env` untracked, `.htaccess` deny as second layer.**
`dbConnect.php:5-27` reads `SALSICCIA_DB_*` via `getenv()` with fair-fallback; `env.inc:3-14` loads `.env` without overriding real env; `.gitignore:10` ignores `.env`; `git ls-files` confirms `.env` is **not tracked**; `.htaccess:7` denies `^\.env$` over HTTP. Matches OWASP guidance to keep secrets out of code and out of version control (OWASP Secrets Management Cheat Sheet, https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html). Keep the pattern; fix only the hardcoded fallbacks (§4, issue 10).

**G2. Fiscal module is the codebase's structural role model: pure builders + isolated transport + non-blocking queue + idempotency.**
`fiscale_build_xml()` (`fiscale.inc:50-67`) and `fiscale_parse_stato()` (`fiscale.inc:70-83`) are pure (no DB/net); `fiscale_trasmetti()` (`fiscale.inc:89-117`) does one attempt, never queues, never throws; `fiscale_fallback()` (`fiscale.inc:139-145`) appends one JSON line; `fiscale_gia_emesso()` (`fiscale.inc:196-208`) gives minimum idempotency; `fiscale_ritenta_coda()` (`fiscale.inc:153-193`) takes an injectable `$trasmetti` callable — i.e. hand-rolled dependency injection for testability. This is exactly the transaction-outbox / fallback pattern: never block the sale on a downstream device. Matches the "Fallback / Coda" domain law in `CONTEXT.md` and modern advice to isolate I/O behind injectable boundaries (PHP-DI "Understanding DI", https://github.com/PHP-DI/PHP-DI/blob/master/doc/understanding-di.md).

**G3. Backoffice list pipeline is injection-safe by construction: whitelist ORDER BY + prepared LIKE + int-clamped pagination.**
`visualizza.php:86-118` builds `ORDER BY` only from `$tabConfig['orders']` whitelist; `:792-832` binds search terms, interpolates only `(int)$offset,(int)$rpp`; same for stats (`stat_dati.inc:55-66`, `:89-97`, `:131-181`). This is the OWASP SQL Injection Prevention "parameterize + allowlist identifiers" rule done right (OWASP Query Parameterization Cheat Sheet, https://cheatsheetseries.owasp.org/cheatsheets/Query_Parameterization_Cheat_Sheet.html). `stat_dati.inc` being shared by page + PDF (`statistiche.php:24`, `stat_pdf.php:128`) is a genuine single-source data layer — extend it, don't duplicate it.

**G4. CSRF tokens on every backoffice/admin POST + output escaping at echo points.**
`csrf_token()/csrf_ok()/csrf_field()` (`funzioni.inc:8-27`) use `random_bytes(32)` + `hash_equals` — the exact OWASP-synchronized-token recipe (OWASP CSRF Prevention Cheat Sheet, https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html; token-generation guidance for PHP 7+, https://devgex.com/en/article/00039777). Every destructive backoffice form carries the token (`visualizza.php:1187,1195,1203,1211,1219`, `backup.php:56`, `functionsFrontend.inc:716,726,863,922`); display data is `htmlspecialchars(…, ENT_QUOTES, 'UTF-8')` at ~30 echo sites (e.g. `functionsFrontend.inc:357,371,436,652`). Keep and extend to the GET-mutation gap (§4, issue 3).

**G5. Failure-open printing + dump-outside-docroot + per-request PDF (no shared files, no races).**
Printer unreachable → order stays `chiuso='0'` and the `stampa_err` branch reuses the same resto layout (`functionsFrontend.inc:1038-1065`, `:1091-1100`); backup resolves outside docroot with random non-guessable filenames (`backup.inc:28-64`); PDF is streamed per-request, never written to disk (`stat_pdf.php:139-140`); print error paths `error_log()` with codes, never payloads (`fiscale.inc:115,131`). Short, deliberate, kiosk-correct engineering.

---

## 4. Issues table (ordered by severity; every row has file evidence)

| Severity | Location | Problem | Why it matters | Best-practice reference |
|---|---|---|---|---|
| Critical | repo root layout; `.htaccess:7-15` | **No `public/` separation: `.inc` source, `.env`, fiscal queue logs live in the docroot**, shielded only by Apache `.htaccess`. Any move to nginx, any `AllowOverride None`, any backup-restore slip exposes source + secrets + receipt amounts over HTTP. | Single config mistake = full source/secret disclosure; OWASP A01 lists `.git`/backup files in web roots as broken access control (OWASP Top 10:2025 A01, https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/) | OWASP A01 "ensure file metadata (.git) and backup files are not present within web roots" (https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/); PHP Configuration Cheat Sheet docroot guidance (https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html) |
| Critical | `reserved/login.php:16-20`; `set.inc:96`; `functionsFrontend.inc:110` | **Weak credential handling, no `password_hash`/Argon2 anywhere** (verified: zero hits for `password_hash\|password_verify` in repo). Backoffice compares against MySQL `PASSWORD()` = double-SHA1 (fast, unsalted-by-app, GPU-crackable); kiosk admin code is a shared plaintext secret (`PWD` constant) compared with `hash_equals` (constant-time compare of a *plaintext* secret — right function, wrong material). | Credential DB leak = immediate crack; shared static code can't be rotated per-user or audited | Use `password_hash(…, PASSWORD_ARGON2ID)` + `password_verify` (PHP manual, https://www.php.net/manual/en/function.password-hash.php); Argon2id is the recommended memory-hard variant (PHP RFC argon2_password_hash_enhancements, https://wiki.php.net/rfc/argon2_password_hash_enhancements) |
| Critical | `functionsFrontend.inc:177-338` (actions `r/a/mq/mr/st/sb/ra`); rendered as GET links e.g. `:439`, `:492-503`, `:532-541` | **State-changing cashier operations over GET with no CSRF token and, for `ra`, no ownership check.** `action=ra&id=` (`:331-338`) reactivates *any* order id (`UPDATE ordini SET chiuso='0' WHERE id_ordine=$id`) — no `id_cassa` scoping, no closed-state validation. `action=mq/mr` mutate any product id on the open order. | On the fair LAN any page/QR/link that triggers these URLs mutates live orders; `ra` can resurrect foreign/closed orders into the current sale | OWASP: GET must be read-only; state changes need unpredictable per-session tokens (OWASP CSRF Prevention Cheat Sheet, https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html); OWASP A01 "enforce record ownership" (https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/) |
| Critical | `funzioni.inc:30-43` (`mysql_query_safe` = raw `mysqli_query`); interpolated call sites `functionsFrontend.inc:179,196,205,211,222,229,233,238,253,259,273,279,295,302,307,318,324,334`; `index.php:20` | **Cashier hot path builds SQL by interpolation; safety rests entirely on remembering `(int)` casts.** One uncast string (e.g. future `$_GET['t']`-style param, a reordered refactor) = SQL injection. The wrapper's name (`*_safe`) actively misleads. | Injection in the money path (order totals, close flags); misleading name suppresses reviewer vigilance | Parameterize every query; never interpolate (OWASP Query Parameterization, https://cheatsheetseries.owasp.org/cheatsheets/Query_Parameterization_Cheat_Sheet.html). The repo already proves it can: `fiscale.inc:221`, `visualizza.php:246-845` |
| Critical | `functionsFrontend.inc:227-243` (INSERT ordini + INSERT righe, then `calcolaTotali` 3 more writes `funzioni.inc:59-87`); close path `:1102-1105` | **Zero transactions anywhere** (verified: no `begin_transaction`/`commit`/`rollback` hits in repo). A failure between INSERT ordini and INSERT righe (or inside `calcolaTotali`'s SELECT→UPDATE→SELECT→UPDATE chain) leaves partial/untotaled orders; concurrent kiosk taps can interleave the read-modify-write in `calcolaTotali`. | Money-data corruption under exactly the load a fair produces; totals can drift from lines | Wrap order mutations in transactions; MySQLi supports `begin_transaction/commit/rollback` (PHP manual mysqli, https://www.php.net/manual/en/mysqli.begin-transaction.php) |
| Major | missing `composer.json` (glob + `git ls-files` confirm); zero `namespace` hits; `declare(strict_types=1)` only in `fiscale.inc:2`, `backup.inc:2` | **No Composer, no PSR-4 autoload, no namespaces, strict types in 2 of ~13 PHP files.** Every dependency is a `require_once` chain (`functionsFrontend.inc:3-6`, `visualizza.php:14-20`, `stat_pdf.php:126-129`); PHP version constraint doesn't exist (**unverified** runtime). | Unreproducible installs, class-name collisions, silent type coercion in money math, no upgrade path to PHP 8.x idioms | PSR-4 autoloading + `declare(strict_types=1)` per file (php-best-practices `type-strict-mode`, https://github.com/AsyrafHussin/agent-skills/blob/main/skills/php-best-practices/rules/type-strict-mode.md); Composer autoload standard (https://getcomposer.org/doc/04-schema.md#autoload) |
| Major | `reserved/visualizza.php` 1468 lines; `functionsFrontend.inc` 1139 lines; `funzioni.inc` 967 lines | **God files mixing routing, SQL, HTML, JS.** `visualizza.php` contains the tab router (`:79-146`), 5× delete handlers (`:260-387`), 5× save handlers (`:469-743`), list queries (`:792-846`), and the full HTML/JS view (`:848-1468`) in one file. `gestisciAzioni()` (`functionsFrontend.inc:98-339`) is controller+model; every `mostra*` is model+view. | SRP violation; a change to one tab risks all five; untestable without a web server + DB | Single Responsibility Principle for PHP (php-best-practices `solid-srp`; SOLID in PHP, https://forklush.com/blog/php-best-practices); split Controller/Service/Repository |
| Major | `global $mysqli[, $cat…]` ×14 in `functionsFrontend.inc:100,344,364,382,403,461,546,599,636,763,823,1032` (+`$id_cassa`, `$HTTP_GET_VARS`) | **No DI container; ambient global connection threaded through every function.** Functions can't be unit-tested (they reach out to the global DB + `$_GET`); swapping the DB handle requires editing call sites. `fiscale_ritenta_coda()`'s injectable `$trasmetti` (`fiscale.inc:153`) shows the team already discovered the fix — once. | Untestable domain logic; hidden coupling; concurrent-request hazards if ever moved off mod_php | Inject dependencies via constructor/callable; containers are optional, DI is not (PHP-DI "Understanding DI", https://github.com/PHP-DI/PHP-DI/blob/master/doc/understanding-di.md) |
| Major | `functionsFrontend.inc:49-64` (`impostaModalitaFiera` rewrites `set.inc` via `file_put_contents`); `visualizza.php:230-236` deliberately avoids including `set.inc` "ha side-effect" | **Config is executable code that the app rewrites at runtime.** `set.inc` mixes constants, `$remoteIP` sniffing (`set.inc:90-95`), and env reads; toggling a flag edits PHP source (race between concurrent requests, opcache staleness, syntax-error-on-crash risk). The `visualizza.php:230-236` workaround (parsing `set.inc` as text!) is the symptom. | Config change can white-screen the kiosk mid-fair; flag reads disagree between processes | Config as inert data (`.env`/array file), never self-modifying code; 12-factor config guidance (https://12factor.net/config) |
| Major | `dbConnect.php:6-27` (fallback `"salsiccia"/"localhost"/"salsiccia"/"Salsiccia@123"`); `set.inc:52,96` (printer IP + `PWD` fallbacks `"192.168.0.205"`, `"@123*"`) | **Hardcoded credential fallbacks committed to git.** If env is unset (fresh XAMPP, container, backup restore), the till runs on public default credentials. `.env` exists locally but the *fallback* is what ships. | Default-credential access to DB + admin panel on any misconfigured deploy | Fail closed when secrets are absent in prod; OWASP Secrets Management (https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html) |
| Major | absent: no `tests/`, no `phpunit.xml`, no `database/` or `*.sql` migration in git (`salsiccia.sql` is gitignored, `.gitignore:5-6`) | **No tests, no migration discipline; schema is unverified tribal knowledge.** Comments admit MyISAM with zero FKs and app-level cascade (`visualizza.php:240`, `:318`). Nothing validates `calcolaTotali` money math, the label builders, or the fiscal XML. | Every refactor is unguarded; schema drift between fair laptops is undetectable | PHP testing pyramid with PHPUnit (https://phpunit.de/documentation.html); versioned migrations over ad-hoc dumps |
| Major | N+1 reads: `funzioni.inc:376-413` (`conta_prodotto` = 1 + N queries per counter) × loop `functionsFrontend.inc:792-796`; per-day loop re-executes in `stat_dati.inc:170-182` | **N+1 query patterns in counters and stats.** Each counter row fans out to per-product `SUM()` queries; the per-day table round-trips once per feast day. Bounded today (5 rows/page, ≤31 days) but the shape doesn't scale and multiplies fair-time DB load. | Latency on the admin screens; DB stampede as product catalog grows | Aggregate in one `GROUP BY` query; lazy-load/paginate at SQL level (php-best-practices `perf-lazy-loading`/`perf-generators` family, https://forklush.com/blog/php-best-practices) |
| Minor | `@` suppression: `env.inc:6`, `fiscale.inc:93,143,157,184,190`, `funzioni.inc:161,168,173` | **Error-suppression operator hides I/O failures** (queue writes, file reads). Failures become silent data loss (a dropped fiscal queue line is unrecoverable money-trail loss). | Silent corruption of the fiscal queue — the one file that must never silently fail | Never use `@`; check return values / try-catch (php-best-practices `error-never-suppress`, https://forklush.com/blog/php-best-practices) |
| Minor | `funzioni.inc:131-150` (`testo_biglietti`, `case 4:` has no `break`, falls through into `case 5:`) | **Switch fall-through bug smell in ticket-text splitter.** `case 4` ends at `:149-150` with no `break`, so 4-word product names also execute the `case 5:` block and overwrite `$str1/$str2`. artisanally "works" only if the case-5 math happens to agree. | Wrong line-wrapping on printed tickets for 4-word names; fragile to any edit | Prefer `match` or early returns (PHP 8 `match`, https://www.php.net/manual/en/control-structures.match.php) |
| Minor | `set.inc:4-5` (`$HTTP_GET_VARS =& $_GET` aliases); no return types anywhere outside new modules; no enums for `tipo`/`chiuso`/`metodo` whitelists repeated at `functionsFrontend.inc:291,1119`, `fiscale.inc:65` | **Legacy idioms + missing modern types.** `$HTTP_*_VARS` is a PHP-4-era alias; order-type/payment-method whitelists are copy-pasted string arrays instead of one enum; zero return/param types in legacy files invites coercion bugs. | Death by a thousand coercions; whitelist drift between the three copies | PHP 8.1 enums + typed signatures (PHP 8.3 release notes on types/`#[\Override]`, https://www.php.net/releases/8.3/en.php; stitcher.io 8.3 guide, https://stitcher.io/blog/new-in-php-83; 2025 overview, https://www.yeasirarafat.com/posts/modern-php-best-practices) |
| Minor | FPDF vendored at `reserved/fpdf/fpdf.php` (in `git ls-files`), version unpinned | **Vendored-by-copy PDF lib with no version record.** Security/bug fixes to FPDF can't be detected or applied systematically; `stat_pdf.php` even extends it (`StatPDF`, `stat_pdf.php:26`). | Unpatchable dependency inside a fiscal-output path | One `composer require setasign/fpdf` (or equivalent) replaces 1 vendored dir with a pinned, auditable version (Composer docs, https://getcomposer.org/doc/01-basic-usage.md) |

---

## 5. Best-practice gap analysis

| Practice (2025–2026 consensus) | Status | Concrete fix location |
|---|---|---|
| `declare(strict_types=1)` in every file | **Partial** (2/13 files: `fiscale.inc:2`, `backup.inc:2`) | Add to `index.php:1`, `dbConnect.php:1`, `set.inc:1`, `env.inc:1`, `funzioni.inc:1`, `functionsFrontend.inc:1`, all of `reserved/` |
| Readonly classes/props, enums, attributes, `match`, constructor promotion (PHP 8.1–8.3) | **Missing** (zero uses; runtime version unverified) | New `src/` value objects first: order-type enum replacing whitelists at `functionsFrontend.inc:291`, `functionsFrontend.inc:1119`, `fiscale.inc:65` |
| PSR-4 autoloading + one class per file + namespace matches tree | **Missing** (zero namespaces; only classes: `StatPDF` in `stat_pdf.php:26` + vendored FPDF) | `composer.json` PSR-4 `Salsiccia\` → `src/`; move `fiscale_*`/`backup_*`/`stat_*` functions into classes file-by-file |
| PSR-12 coding style | **Missing** (mixed brace styles, `isSet`, snake_case functions, HTML-in-echo) | Adopt `squizlabs/php_codesniffer` PSR-12 ruleset; normalize touched files first |
| DI container / constructor injection (PSR-11) | **Missing** (14× `global $mysqli`; injectable callable exists only at `fiscale.inc:153`) | Pass `mysqli` (later a `Db`/`Clock`/`Printer` service) as parameters, starting with `gestisciAzioni()` at `functionsFrontend.inc:98` |
| Service + Repository layers; thin controllers | **Missing** (controller+SQL+HTML fused in `mostra*`/`gestisciAzioni`) | Extract `OrderService` (order flows `:177-339`) and `ProductRepository` (queries `:342-456`) out of `functionsFrontend.inc` |
| `password_hash` Argon2id / `password_verify` | **Missing** | Replace `reserved/login.php:16-20` (SHA1) and `set.inc:96`+`functionsFrontend.inc:110` (plaintext code) |
| Prepared statements everywhere | **Partial** (backoffice+fiscal yes; cashier flows no) | Parameterize `functionsFrontend.inc:177-338` + `index.php:20` + `funzioni.inc:59-87` |
| Transactions for multi-write flows | **Missing** | `functionsFrontend.inc:227-243`, `:259-262`, `funzioni.inc:59-87`, `:1102-1105` close path |
| PSR-3 logging with levels/context | **Missing** (bare `error_log()` strings at 12+ sites) | Centralize behind one `log()` helper first (e.g. wrapping `error_log` with level prefix), called from `fiscale.inc:131,224,230,251`, `funzioni.inc:918-957` |
| Centralized error/exception handling | **Missing** (`display_errors 0` at `funzioni.inc:3-5` only; one `try/catch` at `functionsFrontend.inc:1126-1135`) | Front-controller `set_exception_handler` + shutdown handler in the new bootstrap; keep kiosk-friendly error screen |
| CSRF on all state changes | **Partial** (POST yes; cashier GET mutations no) | `functionsFrontend.inc:177-338` + `index.php:40-101` links |
| AuthZ ownership checks per record | **Missing** (`action=ra` at `functionsFrontend.inc:331-338`) | Scope reactivation/updates by `id_cassa` + state, same as delete paths already do at `:185-187` |
| `public/` web root; secrets outside docroot | **Missing** | Move docroot (see §7); relocate `.env`, `fiscale_*.log`, `label`, `labelCards` |
| Config as data + config caching | **Missing** (executable self-rewriting `set.inc`) | Freeze `set.inc` as read-only defaults; flag state (`MODALITA_FIERA`) into DB or `.env`-adjacent JSON |
| Testing pyramid (unit → integration) | **Missing** | Start with pure builders: `fiscale_build_xml` (`fiscale.inc:50`), `etichetta_continua` (`funzioni.inc:491`), `stat_limiti_giorno` (`stat_dati.inc:38`) — all DB-free already |
| Versioned migrations | **Missing** | `database/migrations/*.sql` checked in; retire gitignored loose dumps (`.gitignore:5-6`) as schema source |
| No `@` suppression | **Partial** (8 sites) | `env.inc:6`, `fiscale.inc:93,143,157,184,190` |
| Dependency manager (Composer) | **Missing** | `composer.json`: php constraint, `setasign/fpdf`, dev: `phpunit/phpunit`, `squizlabs/php_codesniffer` |

---

## 6. IDEAL default PHP application structure (generic reference, framework-agnostic PHP 8.x)

```
myapp/
├── public/                    ← ONLY web-served dir; DocumentRoot points here
│   ├── index.php              ← single front controller: bootstrap → route → respond
│   └── assets/                ← css/js/img (no PHP, no secrets, long cache headers)
├── src/                       ← PSR-4 App\ (one class per file, strict_types everywhere)
│   ├── Controller/            ← thin HTTP adapters: parse input, call services, render/redirect
│   ├── Service/               ← domain use-cases + transactions (OrderService, ReceiptService)
│   ├── Repository/            ← all SQL, prepared, returns entities (OrderRepository…)
│   ├── Domain/                ← enums, value objects, readonly entities (OrderStatus enum…)
│   ├── Http/                  ← router, middleware pipeline (Auth, Csrf), request/response
│   ├── Support/               ← CsrfToken, Logger (PSR-3), Config reader (no side effects)
│   └── View/                  ← templates (escaped output only, zero SQL)
├── config/                    ← inert PHP-array/env config per environment (never self-written)
├── routes/                    ← route table (method+path → controller), no logic
├── database/
│   ├── migrations/            ← versioned schema changes, up/down, applied in order
│   └── seeds/                 ← dev/fair-demo data only
├── tests/
│   ├── Unit/                  ← pure domain/service tests, no DB (fast, many)
│   ├── Integration/           ← DB/HTTP/printer-mock tests (slower, fewer)
│   └── fixtures/              ← sample rows, fake fiscal XML
├── storage/                   ← OUTSIDE docroot: logs, queue files, dumps, print spool
├── vendor/                    ← Composer deps (never hand-vendored libs)
├── .env.example               ← documented keys, no values; real .env gitignored
├── composer.json              ← php constraint, PSR-4 map, scripts (test, cs, migrate)
└── phpunit.xml                ← test suites mapping tests/Unit + tests/Integration
```

---

## 7. RECOMMENDED layout for MY project (concrete moves)

```
SalsicciaStagisti/
├── public/                    ← NEW: DocumentRoot moves here
│   ├── index.php              ← FROM root index.php (trimmed to bootstrap+dispatch+layout only)
│   ├── reserved/              ← MOVE reserved/{login,visualizza,statistiche,stat_pdf,backup}.php here
│   │                            (keep filenames/URLs stable: /reserved/*.php still resolves)
│   ├── style.css              ← MOVE as-is
│   └── asset/                 ← MOVE as-is (banknote images keep same relative URLs)
├── src/                       ← NEW: PSR-4 Salsiccia\
│   ├── Cassa/OrderService.php ← FROM gestisciAzioni() + calcolaTotali(): add/sb/close/standby in transactions
│   ├── Cassa/PrintService.php ← FROM genera_file_stampa()+invia_file_stampa()+ftpPut(): spool→storage/, send
│   ├── Fiscale/Fiscale.php    ← FROM fiscale.inc (already modular: build/parse/trasmetti/coda/retry)
│   ├── Backup/BackupRun.php   ← FROM backup.inc (unchanged logic, injected mysqli+cfg)
│   ├── Stats/StatsData.php    ← FROM stat_dati.inc (already the shared layer; add per-day single-query)
│   ├── Catalog/CatalogRepo.php← FROM mostraNavCategorie/mostraTabellaProdotti/mostraTitolo queries
│   └── Support/{Config,Csrf,Db}.php ← FROM env.inc+set.inc(reads only)+dbConnect.php+csrf_* helpers
├── config/cassa.php           ← NEW: FROM set.inc constants as inert array (printer/layout/feast)
├── routes/cassa.php           ← NEW: action→handler table replacing the if-chain (gestisciAzioni :168-338)
├── database/migrations/       ← NEW: first migration = current schema dump (replaces tribal schema)
├── tests/Unit/                ← NEW: FiscaleTest, EtichettaContinuaTest, StatLimitiTest (pure fns first)
├── storage/                   ← NEW (outside docroot): label spool, fiscale_coda/inviati/mock, dumps
├── resources/                 ← KEEP (printer manuals stay)
├── reserved/fpdf/             ← DELETE after `composer require` FPDF (kill vendored copy)
├── .env.example               ← NEW: document SALSICCIA_* keys (values stay in untracked .env)
├── composer.json / phpunit.xml← NEW: php ^8.3, PSR-4 map, scripts
└── root *.inc/dbConnect.php   ← DELETE after moves (thin require shims during transition only)
```

**Why per major move (2–3 sentences each):**

- **`public/` split first.** Today `.htaccess:7-15` is the *only* thing between the internet and `.env`/`.inc`/fiscal logs. Moving the docroot makes that entire failure class structurally impossible (nginx-safe, backup-safe), which is why every modern PHP layout (Composer, Symfony, Laravel, Laminas) starts here. Nothing else in the plan matters if source disclosure is one directive away.
- **`src/` + Composer, seeded from the already-good modules.** `fiscale.inc`, `backup.inc`, `stat_dati.inc` already have typed signatures, pure functions, and (for retry) an injectable callable — they become namespaced classes almost mechanically, proving the pattern before touching the 1468-line `visualizza.php`. Composer simultaneously deletes the vendored `reserved/fpdf/` and pins the PHP version this kiosk actually runs (currently **unverified**).
- **Split `functionsFrontend.inc` into `OrderService` + `CatalogRepo` + templates.** The file's 14 `global $mysqli` imports and fused SQL-echo functions are why nothing there is testable; extracting the order flows (`:177-339`) into a service with injected DB also creates the single place to add the missing transactions and the `id_cassa` ownership checks. Views keep only escaped echo, matching what the backoffice already does well.
- **Freeze `set.inc` into `config/cassa.php` + DB-backed flags.** The runtime self-rewrite (`impostaModalitaFiera`, `functionsFrontend.inc:49-64`) is the strangest construct in the repo — and `visualizza.php:230-236` already works around it by parsing PHP as text. Moving `MODALITA_FIERA`/cassa selection into the DB (or a tiny JSON in `storage/`) removes the race, the opcache hazard, and the text-parsing hack in one move.
- **`storage/` for spool/queue/dumps + `database/migrations/`.** Fiscal queue lines and label files are money-trail and print-spool, not web content — they belong outside the docroot with the backups (`backup.inc:28-58` already aims there). A first checked-in migration captures the MyISAM schema that today exists only on fair laptops, ending "works on my XAMPP" drift.

---

## 8. Prioritized action plan (file/line pointers, no rewrites)

**Phase 1 — safety/correctness (before the next fair):**
1. Add `id_cassa` + state scoping to `action=ra` reactivation — `functionsFrontend.inc:331-338` (expected payoff: foreign/closed orders can no longer be resurrected into the live sale).
2. Wrap order write-chains in transactions — `functionsFrontend.inc:227-243`, `:259-262`, `funzioni.inc:59-87` `calcolaTotali`, close path `:1102-1105` (payoff: no more partial/untotaled orders on mid-flow failure).
3. Parameterize the cashier interpolations — `functionsFrontend.inc:177-338`, `index.php:20`, `funzioni.inc:59-87` (payoff: kills the highest-likelihood injection surface; backoffice pattern at `visualizza.php:246-845` is the template).
4. Fail closed on missing secrets; remove committed fallbacks — `dbConnect.php:6-27`, `set.inc:52,96` (payoff: fresh/restored deploys can't silently run on public default credentials).
5. Fix `case 4` fall-through in `testo_biglietti` — `funzioni.inc:131-150` (payoff: correct 4-word ticket wrapping; 5-line change).
6. Replace credential verification with `password_hash`/`password_verify` (Argon2id) + migration path for existing backoffice hashes — `reserved/login.php:16-20`, `set.inc:96` + `functionsFrontend.inc:103-128` (payoff: credential DB leak stops being game-over).
7. Convert cashier GET mutations to POST+CSRF (keep kiosk UX: auto-submit buttonsForms carry `csrf_field()` like the admin panel already does at `functionsFrontend.inc:715-719`) — `functionsFrontend.inc:177-338` + links in `index.php:40-101` (payoff: fair-LAN CSRF/link-prefetch can't mutate orders).

**Phase 2 — structure (between fairs):**
8. Create `composer.json` (php constraint after measuring the fair XAMPP runtime — currently **unverified**), PSR-4 `Salsiccia\→src/`, require FPDF via Composer, delete `reserved/fpdf/` (payoff: reproducible installs, patchable PDF lib).
9. Create `public/` + move entry points/assets; point Apache `DocumentRoot` there; relocate `.env`, `fiscale_*.log`, `label*`, `cmd/` to `storage/` (payoff: removes the Critical disclosure class; `.htaccess` becomes belt-and-braces).
10. Freeze `set.inc` → `config/cassa.php` (read-only) + move `MODALITA_FIERA`/cassa flags to DB/`storage/`; delete `impostaModalitaFiera`'s self-rewrite (`functionsFrontend.inc:49-64`) and the text-parsing workaround (`visualizza.php:230-236`) (payoff: no more source-rewriting races or opcache staleness).
11. Extract `OrderService` + `CatalogRepo` from `functionsFrontend.inc:98-339,342-456`; inject `mysqli` (payoff: order logic becomes unit-testable; single home for transactions/ownership).
12. Split `visualizza.php:1-1468` into per-tab controllers + one list/form template (payoff: a 5-tab change stops risking the other four tabs).
13. Check in `database/migrations/0001_schema.sql` from the live fair DB; document MyISAM→InnoDB decision explicitly (FKs would delete the app-level cascade at `visualizza.php:260-387`) (payoff: schema becomes reviewable, diffable, restorable).
14. Add `declare(strict_types=1)` + param/return types file-by-file, legacy files first in money order: `funzioni.inc`, `functionsFrontend.inc`, `index.php`, `dbConnect.php` (payoff: coercion bugs in totals surface as loud TypeErrors in rehearsal, not silent drift at the fair).

**Phase 3 — polish (when Phase 2 is green):**
15. PSR-3 logger facade over `error_log` with levels; drop all `@` suppressions — `env.inc:6`, `fiscale.inc:93,143,157,184,190` (payoff: queue/file failures become visible with context instead of silent).
16. Collapse the N+1 counters into single `GROUP BY` queries — `funzioni.inc:376-413` × `functionsFrontend.inc:792-796`; single-statement per-day aggregation — `stat_dati.inc:170-182` (payoff: admin screens stay fast as catalog grows).
17. One `OrderType`/`PayMethod` enum replacing the three string whitelists — `functionsFrontend.inc:291`, `functionsFrontend.inc:1119`, `fiscale.inc:65` (payoff: whitelist drift becomes a compile-time impossibility).
18. PHPUnit harness starting with the already-pure functions — `fiscale.inc:50,70`, `funzioni.inc:491`, `stat_dati.inc:38,46,55` (payoff: first regression net over money math, fiscal XML, and fiscal-day boundaries; the injectable `$trasmetti` at `fiscale.inc:153` makes queue tests DB-free).
19. Session hardening pass: `session.cookie_httponly/secure/samesite`, regenerate-on-privilege (already done at `functionsFrontend.inc:112`, `reserved/login.php:26`), server-side (not session-only) rate limits for `reserved/login.php:10-15` (payoff: backoffice login resists distributed guessing; **unverified** against prod `php.ini`).

---

## 9. Sources (everything consulted)

1. PHP 8.3 release announcement (typed constants, `#[Override]`, `json_validate`, readonly amend) — https://www.php.net/releases/8.3/en.php
2. Stitcher.io "What's new in PHP 8.3" — https://stitcher.io/blog/new-in-php-83
3. PHP Architect "What's New and Exciting in PHP 8.3" — https://www.phparch.com/2023/08/whats-new-and-exciting-in-php-8-3
4. "What's New in PHP 8.4 (and 8.5)" slides (asymmetric visibility) — https://talks.php.net/show/php-phpday25/3
5. Yeasir Arafat "Modern PHP Best Practices in 2025" — https://www.yeasirarafat.com/posts/modern-php-best-practices
6. Forklush "PHP Best Practices for Modern Web Development in 2025" (enums, fibers, `match`, DI, PSR) — https://forklush.com/blog/php-best-practices
7. Cambridge Infotech "PHP Best Practices 2026" — https://cambridgeinfotech.io/php-best-practices
8. php-best-practices `type-strict-mode` rule (`declare(strict_types=1)` semantics) — https://github.com/AsyrafHussin/agent-skills/blob/main/skills/php-best-practices/rules/type-strict-mode.md
9. PHP manual `password_hash` (bcrypt/Argon2i/Argon2id, options, cost changes) — https://www.php.net/manual/en/function.password-hash.php
10. PHP RFC "Argon2 Password Hash Enhancements" (Argon2id recommended) — https://wiki.php.net/rfc/argon2_password_hash_enhancements
11. Bill Millan / asecuritysite "The Proper Password Hasher: Argon2" (memory-hardness rationale) — https://asecuritysite.com/blog/2025-02-18_The-Proper-Password-Hasher-and-Memory-Buster--Argon2-3d5924a9d5b4.html
12. PHP-DI "Understanding Dependency Injection" (DI vs container, SRP-friendly wiring) — https://github.com/PHP-DI/PHP-DI/blob/master/doc/understanding-di.md
13. OWASP CSRF Prevention Cheat Sheet (synchronized tokens, `random_bytes`+`hash_equals`, GET-read-only) — https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
14. OWASP WSTG "Testing for CSRF" — https://owasp.org/www-project-web-security-testing-guide/v42/4-Web_Application_Security_Testing/06-Session_Management_Testing/05-Testing_for_Cross_Site_Request_Forgery
15. DevGex "Secure Implementation and Best Practices for CSRF Tokens in PHP" (2025) — https://devgex.com/en/article/00039777
16. OWASP Top 10:2025 — https://owasp.org/Top10/2025
17. OWASP A01:2025 Broken Access Control (record ownership, `.git`/backup files in web roots, rate limiting) — https://top10.owasp.org/2025/A01_2025-Broken_Access_Control/
18. OWASP Query Parameterization Cheat Sheet — https://cheatsheetseries.owasp.org/cheatsheets/Query_Parameterization_Cheat_Sheet.html
19. OWASP Secrets Management Cheat Sheet — https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html
20. PHP manual `mysqli::begin_transaction` — https://www.php.net/manual/en/mysqli.begin-transaction.php
21. PHP manual `match` expression — https://www.php.net/manual/en/control-structures.match.php
22. Composer basic usage / schema-autoload — https://getcomposer.org/doc/01-basic-usage.md and https://getcomposer.org/doc/04-schema.md#autoload
23. PHPUnit documentation — https://phpunit.de/documentation.html
24. 12-Factor App: Config — https://12factor.net/config

*Using php-best-practices skill to structure the type/PSR/SOLID/error/security checks. No files read beyond the PHP scope except where PHP references them (asset image paths in echo, `resources/linkLabel` excluded — non-PHP). Missing-info items (PHP runtime version, prod `php.ini`, live DB engine/charset, FPDF version) are marked `unverified` above rather than assumed.*
