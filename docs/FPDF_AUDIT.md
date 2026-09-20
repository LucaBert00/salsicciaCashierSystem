# FPDF vendored-version audit + Composer replacement path (T03)

Question (issue #13): what FPDF version is vendored at `reserved/fpdf/`, and what is the exact Composer replacement?
No code changed here — research for T15/T31 to execute. Refs: `PHP_STRUCTURE_REVIEW.md` §4 Minor row 16, §5 Composer row, §8-8, §7 `reserved/fpdf/` DELETE row. Map: #10.

## 1. Vendored version: FPDF 1.6 (2008-08-03), IDENTIFIED (not unknown)

- Header: `reserved/fpdf/fpdf.php:2-10` declares `Version: 1.6`, `Date: 2008-08-03`, `Author: Olivier PLATHEY`, and `define('FPDF_VERSION','1.6')` at `:10`. This matches upstream FPDF 1.6 release date (fpdf.org history; Setasign/FPDF tags).
- Provenance: `fpdf.php:11-13` (comment from #95) states vendored from legacy `1.0/pdf/fpdf.php`, byte-identical except 3 PHP 8 micro-patches marked `PHP8`:
  1. `:80-81` — explicit `__construct` forwarding to legacy `FPDF()` constructor (PHP 8 no longer calls same-name constructors).
  2. `:1081-1083` — `get_magic_quotes_runtime()` guarded by `function_exists` (removed since PHP 5.4 / gone in PHP 8).
  3. `:1573-1574` — `each()` replaced with `foreach` over `$this->images` in `_putimages()` (`each()` removed in PHP 8).
- No changelog file ships in `reserved/fpdf/` — dir contains only `fpdf.php` + `font/helvetica.php` + `font/helveticab.php` (subset of upstream core metrics; sufficient because `stat_pdf.php` uses only `Arial` regular/bold, aliased to helvetica at `fpdf.php:467,517-518`).
- So the "header/changelog diff" acceptance resolves to: **version identified from header as upstream 1.6 + 3 local PHP 8 patches, no CHANGELOG vendored**.

## 2. Replacement: `setasign/fpdf` with constraint `^1.8`

Exact line for T15/T31:

```sh
composer require "setasign/fpdf:^1.8"
```

- Package: `setasign/fpdf` — official Composer mirror of fpdf.org releases, MIT, 78M+ installs, 0 advisories (packagist.org/packages/setasign/fpdf, fetched 2026-09-20).
- Constraint rationale: `^1.8` admits `<2.0`, resolving to latest **1.9.0 (2026-05-31)** today; floor 1.8.x keeps the PHP-compat bar low for the still-unverified fair XAMPP runtime. Upstream README's narrower `^1.9` is equivalent-today.
- Autoload compat: its `composer.json` uses `"classmap": ["fpdf.php"]`, exposing the **global class `FPDF`** with no namespace (raw.githubusercontent.com/Setasign/FPDF/master/composer.json) — so `class StatPDF extends FPDF` (`stat_pdf.php:26`) keeps working once `stat_pdf.php:129` (`require_once __DIR__ . '/fpdf/fpdf.php'`) is swapped for `vendor/autoload.php`.
- Pre-conditions for T15/T31 on the fair host: PHP extensions `ext-zlib` + `ext-gd` (package `require`), then delete `reserved/fpdf/` per §7.
- Not done here: no `composer.json` created, vendored copy left in place (execution belongs to T15/T31).

## 3. API-surface check (`StatPDF`, `stat_pdf.php:26`): YES, compatible

Every FPDF member touched by `stat_pdf.php` exists with unchanged signature in upstream 1.7/1.8/1.9 (stable public API since 1.6):

| Member used in `stat_pdf.php` | Vendored definition | Upstream ≥1.8 status |
|---|---|---|
| `new StatPDF()` → `__construct('P','mm','A4')` | `fpdf.php:78-83` | Same defaults, same signature |
| `SetFont('Arial', …)` | `fpdf.php:509` (+ Arial→helvetica alias `:517-518`) | Alias preserved |
| `Cell($w,$h,$txt,$border,$ln,$align)` | `fpdf.php:623` | Same signature |
| `Ln()` | `fpdf.php:896` | Same |
| `SetY(-15)` (Footer) | `fpdf.php:983` | Same |
| `PageNo()` (Footer) | `fpdf.php:384` | Same |
| `SetAutoPageBreak(true, 18)` | `fpdf.php:200` | Same |
| `AddPage()` | `fpdf.php:305` | Same |
| `Output('stat-….pdf', 'I')` | `fpdf.php:1000` | Same `'I'` (inline) destination |
| `Header()` / `Footer()` overrides | base stubs `fpdf.php:374-379` | Same override pattern |

Notes:

- Only core fonts + text/tables are used (no images, no AddFont, no libchart) — the Composer package's full `font/` dir is a superset of the 2 vendored metric files, no regression.
- The 3 local PHP 8 patches (§1) are subsumed by upstream ≥1.7/1.8 fixes; deleting the vendored copy removes, not loses, them.
- The `stat_pdf_testo()` CP1252//TRANSLIT path (`stat_pdf.php:13-18`) is orthogonal to the library version (core-font latin-1 behaviour unchanged upstream).
- One runtime check remains for T15/T31: confirm `ext-gd` + `ext-zlib` on the fair XAMPP host (no `php` binary in this shell to verify).
