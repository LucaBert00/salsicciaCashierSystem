# ARCHITETTURA_REVISTA.md — salsicciaCashierSystem

> **Stato**: documento di riferimento unico per l'architettura del progetto.
> Sostituisce `PHP_STRUCTURE_REVIEW.md`, `docs/INC_REVIEW.md`, `FEATURE_GAP_ANALYSIS.md`,
> `LEGACY_GRUPPO5_ANALISI.md`, `PORTING_ANALYSIS.md` e `docs/FPDF_AUDIT.md` (rimossi).
> **Data**: 2026-09-29. **Base**: codice reale a quel giorno + ricerca online (§8).
> **Vincolo di progetto**: il software gira su casse Debian, in fiera, senza rete,
> con `systemd`, CUPS, `sudo` e stampante termica. Nessuna proposta qui introduce
> framework, DI container, template engine o build step.

> **Nota sui riferimenti**: le due review precedenti erano citate nei commenti di
> `routes/cassa.php`, `src/Support/ErrorHandler.php`, `composer.json`, `phpcs.xml`
> e negli header `T31` dei cinque file legacy. Tutti quei riferimenti sono stati
> riallineati a questo documento nello stesso commit che ha rimosso le review.

---

## 1. Comprensione della repository

### Cosa fa

Kiosk touch per cassa bar/fiera: catalogo prodotti per categorie, ordine, riepilogo,
stampa su termica (ZPL/EPL via CUPS o FTP), scontrino fiscale via registratore di rete
con *fallback* + *coda*, pannello admin su schermate protette da codice Argon2id,
backoffice CRUD 5 tab in `reserved/`, statistiche, PDF, backup.

### Struttura reale

```text
salsicciaCashierSystem/
├── public/                    ← DocumentRoot (Apache/XAMPP) — l'unica dir servita
│   ├── index.php              134 righe — front controller cassa
│   ├── style.css              1257 righe
│   ├── asset/                 soldi/, sfondo, favicon
│   └── reserved/              backoffice (login, visualizza, statistiche, stat_pdf, backup)
├── env.inc        763 righe   ← .env loader, session, throttle, printer gate, CUPS probe, flags
├── functionsFrontend.inc 1588  ← TUTTO il frontend cassa
├── funzioni.inc   894 righe   ← CSRF, DB, calcolo totali, builder etichette
├── set.inc        150 righe   ← costanti deploy + fail-closed
├── dbConnect.php   19 righe   ← mysqli_connect da env, die() se manca
├── src/           Salsiccia\  PSR-4, 15 classi: Cassa, Catalog, Fiscale, Stats,
│                             Backoffice, Backup, Support  ← qui vive il codice BUONO
├── routes/cassa.php            tabella action→handler
├── config/cassa.php            default inerti
├── database/migrations/*.sql   3 migration
├── tests/                      9 file PHPUnit, 59 test
├── storage/                    fuori docroot: spool, coda, dump, stato
└── reserved/auth_password.inc  helper password legacy
```

PHP richiesto: `^8.2` (`composer.json`); runtime di sviluppo misurato 8.2.12.

### Come funziona davvero una richiesta

Tracciato reale di `GET /index.php?action=a&id=7`:

```text
public/index.php
 ├─ riga 2  require src/Support/ErrorHandler.php  → registra()
 ├─ riga 4  require functionsFrontend.inc   ◄── QUI SCOPRE TUTTO
 │            ├─ riga 5  require set.inc      → define() + può die(500)
 │            ├─ riga 6  require dbConnect.php → env.inc + mysqli_connect (o die)
 │            ├─ riga 7  require funzioni.inc  → ini_set(), CSRF, DB helpers
 │            ├─ riga 8-14 require 7 classi src/
 │            ├─ riga 17 salsiccia_session_start()
 │            ├─ riga 58 $_GET['cat'] → riga 60 defaultCat($mysqli)   [QUERY]
 │            ├─ riga 64-67 $but_x_row/$but_x_col/$bot_width          [crea le globali]
 │            └─ riga 70 gestisciAzioni($mysqli, $id_cassa)           [MUTAZIONE!]
 │                 └─ riga 338 require routes/cassa.php
 │                    riga 342-345 $handler($mysqli, $id_cassa)
 │                       → OrderService::aggiungiProdotto()  [INSERT + transazione]
 │
 ├─ riga 6  $action = $_GET['action']
 ├─ riga 9  require routes/cassa.php  ◄── SECONDO caricamento dello stesso file
 ├─ riga 12-24 12 boolean $show*
 ├─ riga 26 $isCassaMain = !A && !B && !C ... (12 termini negati)
 ├─ riga 28-34 query tinta categoria
 ├─ riga 36 controllo isAdmin con lista hardcoded di 7 action
 └─ riga 53-132 HTML: 14 elseif/else che chiamano mostra*()
```

**Il punto chiave: `index.php` non è il vero entry point.** Le righe 6–40 di `index.php`
vengono eseguite *dopo* che `functionsFrontend.inc` ha già fatto bootstrap, connessione
DB, risoluzione categoria e dispatch delle mutazioni. Il file che contiene la logica di
avvio è un `.inc` incluso per side-effect.

Prova irrefutabile: `tests/Unit/PowerF2Test.php:24-29` deve fare `file_get_contents()` +
`preg_match()` + **`eval()`** del corpo di due funzioni per poterle testare, perché il
file «NON e' richiedibile in isolamento (require dbConnect con die + sessione +
gestisciAzioni a top-level)».

---

## 2. Problemi architetturali principali

| # | Problema | Evidenza | Gravità |
|---|---|---|---|
| P1 | **Entry point non onesto**: bootstrap e dispatch vivono in un `.inc` incluso per side-effect | `functionsFrontend.inc:5-70` | Alta |
| P2 | **Routing triplicato**: la stessa lista di schermate esiste in 3 forme | `index.php:12-24` + `index.php:26` + `index.php:36` + `routes/cassa.php` | Alta |
| P3 | **Viste che scrivono**: 4 funzioni `mostra*()` fanno scritture DB/filesystem/HTML-sorgente | `:1083-1127`, `:1023-1056`, `:1214-1233`, `:1482-1588` | **Critica** |
| P4 | **`set.inc` riscritto a runtime** — sorgente PHP modificato senza lock né rename atomico | `functionsFrontend.inc:1083,1116-1127` | **Critica** |
| P5 | **`env.inc` non è più un file env**: 575/763 righe sono stampante+CUPS | `env.inc:139-714` | Media |
| P6 | **4 funzioni morte** (~100 righe) | `funzioni.inc:84,412,448,459` | Bassa |
| P7 | **`ini_set()` in un include** colpisce anche i test | `funzioni.inc:7-9` | Bassa |
| P8 | **`mysql_query_safe`** resta un wrapper raw con 9 call site | `funzioni.inc:37-50` | Media |

### P3 e P4 nel dettaglio — questi sono i due veri problemi

**`mostraSchermataStampa()`** (`functionsFrontend.inc:1482-1588`) ha nome di vista ma:
- `:1523` interroga il DB
- `:1535` genera il file di stampa
- `:1553` **`UPDATE ordini SET chiuso = 1`** — chiude l'ordine
- `:1577` **`Fiscale::emettiScontrino()`** — emette il documento fiscale

È un use-case completo mascherato da `mostra*`. È la ragione per cui `index.php` non
può essere "solo routing + view": la riga 60 non chiama un renderer, chiama un caso d'uso.

**`mostraModificaInfo()`** (`functionsFrontend.inc:1083-1127`) legge `set.inc` riga per
riga, e su POST lo **riscrive con `file_put_contents`** senza `LOCK_EX`, senza
tmp+rename, senza verifica della sintassi risultante. L'header di `set.inc:2` dice
esplicitamente «set.inc resta congelato read-only, mai rewrite» — l'invariante
documentata è **violata dal codice**. Un crash a metà scrittura, o un valore con
carattere inatteso, ⇒ schermata bianca su tutte le casse fino al ripristino manuale.
È esattamente l'anti-pattern che l'override di `MODALITA_FIERA` aveva già corretto
spostandolo in `storage/cassa_flags.json` (T17), **reintrodotto da #75**.

---

## 3. `index.php` — Analisi

**Metà giusta, metà sbagliata della preoccupazione iniziale.**

`index.php` **non contiene logica applicativa e non contiene una sola query**. Sulla metà
"troppe righe / troppa business logic" la preoccupazione non è confermata: sono 134
righe, nessun SQL, nessuna scelta di dominio. Il refactor T23 ha già spostato il grosso.

Il problema reale è un altro, ed è più subdolo.

### 3.1 Le responsabilità attuali

| Riga | Responsabilità | Giudizio |
|---|---|---|
| 2-3 | Registrazione error handler | **corretto** per un front controller |
| 4 | Bootstrap + sessione + DB + dispatch | **fuori luogo** — è dentro l'include |
| 6-10 | Validazione action contro routes | **corretto**, ma duplicato (§3.2c) |
| 12-24 | 12 boolean `$show*` | **codice fragile** |
| 26 | `$isCassaMain` con 12 `&&` negati | **peggio: lista negativa da mantenere** |
| 28-34 | Query colore categoria | **una query in più per sapere un colore** |
| 36-40 | Authz: lista hardcoded di 7 action + redirect | **terza copia della lista schermate** |
| 53-132 | HTML: shell + `main` + `aside` | **legittimo, ma mescolato al routing** |

### 3.2 Le 5 criticità concrete

**(a) La catena di boolean è una lista negativa da mantenere a mano.**
`index.php:26`:

```php
$isCassaMain = !$azioneNonValida && !$showAdmin && !$showStampa && !$showModifica
    && !$showOpzioni && !$showStandby && !$showRepair && !$showContatori
    && !$showPrintReset && !$showSwitchPrinter && !$showInfo && !$showRestart
    && !$showShutdown && !($showConfig && isset($_GET['ok']) && !$showAdmin);
```

Aggiungere una schermata = ricordarsi di editare **tre** posti: il flag, questa catena, e
la lista admin alla riga 36. Dimenticarne uno non dà un errore: dà una schermata con lo
sfondo sbagliato, o un pannello admin raggiungibile senza login. È un difetto che si
manifesta in fiera, non in sviluppo.

**(b) L'autorizzazione è in tre strati sovrapposti.**
- `index.php:36` — lista hardcoded `array('repair','print_reset','contatori','switch_printer','info','restart','shutdown')`
- `index.php:15,18-24` — ogni flag admin già ha `&& $isAdmin`
- `mostraContatori:969`, `mostraRipristinaDb:1026`, `mostraSwitchPrinter:1259`,
  `mostraPower:914`, `mostraModificaInfo:1078` — ognuna rifà
  `if (!isAdmin()) { header(...); exit; }`

Le prime due copie sono la stessa informazione. La terza è una difesa in profondità
legittima e va tenuta.

**(c) `routes/cassa.php` viene caricato due volte** — `index.php:9` e
`functionsFrontend.inc:338`. Non un bug, ma è il sintomo che nessuno dei due possesses la
tabella.

**(d) Le variabili globali sono un contratto implicito.**
`index.php:113` usa `$but_x_row` e `$but_x_col`. Non sono dichiarate, non sono parametri,
non sono documentate: sono create a `functionsFrontend.inc:64-65`. Lo stesso vale per
`$mysqli` (riga 32) e `$cat` (riga 30). Un refactor che tocca la testa dell'include rompe
`index.php` con un errore in un punto che non c'entra.

**(e) `?action=s` non è una vista.** `index.php:60` chiama `mostraSchermataStampa()`, che
stampa su hardware, chiude l'ordine e emette lo scontrino fiscale. Finché questo resta
comesso, `index.php` non può essere ridotto a routing puro, perché la sua "vista" ha
effetti monetari.

### 3.3 Dipendenze

`functionsFrontend.inc` → `set.inc` → `env.inc`; → `dbConnect.php` → `env.inc`;
→ `funzioni.inc` → `env.inc`; → 7 classi `src/` → 3 di loro richiedono `funzioni.inc` o
`env.inc` con `dirname(__DIR__,2)`. **Il grafo è ciclico a livello di file**
(`src/Cassa/OrderService.php:12` richiede `funzioni.inc`, che è richiesto da
`functionsFrontend.inc`, che richiede `OrderService.php`).

### 3.4 Verdetto

`index.php` oggi è **un front controller che finge di essere un front controller**. Non ha
troppa logica: ha la logica sbagliata nel posto sbagliato, più una duplicazione di
dispatch che nessuno dei due file possiede.

---

## 4. `index.php` — Soluzione proposta

### Cosa deve restare

- `ErrorHandler::registra()` (già fatto, giusto)
- `require bootstrap.php`
- **Una** chiamata che risolve la action
- **Una** chiamata che renderizza
- Lo `<html>` / `<head>` / `<aside>` (è markup puro, il posto giusto è il front
  controller, o un `layout()` di una view)

### Cosa deve andarsene

| Da spostare | Dove |
|---|---|
| `require functionsFrontend.inc` (riga 4) | `bootstrap.php` |
| 12 boolean `$show*` + catena di 12 `&&` | `routes/cassa.php` (una riga per schermata) |
| Lista admin hardcoded (riga 36) | `routes/cassa.php`, campo `auth` |
| Query tinta categoria (28-34) | `CassaView::render()` |
| Dispatch mutazioni | `CassaController::gestisci()` |
| 14 `elseif` con `mostra*()` | `CassaView::render()` con un `match` |
| Globali `$cat`/`$but_x_row`/`$mysqli` impliciti | parametri espliciti |

### Struttura target: `routes/cassa.php` come unica fonte di verità

```php
return array(
    'a'    => array('handler' => 'aggiungi',      'view' => 'cassa',     'auth' => null),
    'm'    => array('handler' => null,             'view' => 'modifica',  'auth' => null),
    'o'    => array('handler' => null,             'view' => 'opzioni',   'auth' => null),
    's'    => array('handler' => 'stampa',         'view' => 'resto',     'auth' => null),
    'c'    => array('handler' => 'login',          'view' => 'config',    'auth' => null),
    'repair'         => array('handler' => null,   'view' => 'repair',    'auth' => 'admin'),
    'contatori'      => array('handler' => null,   'view' => 'contatori', 'auth' => 'admin'),
    'switch_printer' => array('handler' => 'salvaStampante', 'view' => 'switchPrinter', 'auth' => 'admin'),
    'info'           => array('handler' => 'salvaFesta',     'view' => 'info', 'auth' => 'admin'),
    'restart'        => array('handler' => null,   'view' => 'restart',   'auth' => 'admin'),
    // ...
);
```

Una riga per schermata. Aggiungere una schermata = **una riga**, non tre. `auth`
centralizza l'autorizzazione; `view` sostituisce i 12 boolean; `handler` sostituisce
`gestisciAzioni()`.

### `index.php` finale

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Salsiccia\Cassa\CassaController;
use Salsiccia\Cassa\CassaView;
use Salsiccia\Support\Db;

$db = Db::connessione();
CassaView::render(CassaController::gestisci($db), $db);
```

**Da 134 righe a 8.** E soprattutto: `CassaView::render()` non riceve mai un `$_GET` da cui
decidere cosa scrivere — riceve un nome di schermata già validato e autorizzato.

### `bootstrap.php` (nuovo, ~40 righe, root)

```php
<?php
declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
\Salsiccia\Support\ErrorHandler::registra();
\Salsiccia\Support\Env::carica();          // da env.inc:1-30 (loader .env + cassa_log)
\Salsiccia\Config\CassaConfig::carica();   // costanti vive, da set.inc
\Salsiccia\Support\Session::start();
define('CASSA_DB', \Salsiccia\Support\Db::connetti());   // da dbConnect.php, fail-closed
```

Sostituisce 4 `require_once` concatenati in ognuno dei 5 entry point, e rende visibile
l'ordine di inizializzazione. **Bonus decisivo**: `functionsFrontend.inc` diventa
richiedibile in isolamento → `PowerF2Test` non usa più `eval()`.

### Perché non un framework

Verificato con la ricerca (§8): il tutorial front-controller ufficiale di Symfony mostra
che il principio è `Request → matcher → controller → Response` in ~100 righe **senza
dipendenze**. Laravel fa la stessa cosa con `public/index.php` di 24 righe che delega
tutto a `bootstrap/app.php`. Su una cassa Debian in fiera (niente rete, `systemd`, CUPS,
`sudo`, builder ZPL/EPL hand-written, boot < 1s), un framework aggiunge kernel HTTP,
container DI, cache layer e un albero di dipendenze da `composer install` su ogni till —
a fronte di zero vantaggio. **Il principio sì, il framework no.**

---

## 5. `.inc` — Analisi

### Misure reali (e due verdetti ormai superati)

| File | INC_REVIEW (2026-09-21) | Oggi | Delta | Verdetto vecchio | Verdetto oggi |
|---|---|---|---|---|---|
| `env.inc` | 205 | **763** | **+558** | «accettabile, tieni» | **Sbagliato** |
| `functionsFrontend.inc` | 1027 | **1588** | **+561** | «split, 3 livelli fusi» | **Corretto, ma understatement** |
| `funzioni.inc` | 892 | 894 | +2 | «split, 6 responsabilità» | **Corretto** |
| `set.inc` | 117 | 150 | +33 | «tieni, solo trim» | **Da rifare** |

`env.inc` è triplicato per colpa di F3/F4 (carta stampa + CUPS) landing dopo quella
review. La sua mezza funzione `salsiccia_storage_path()` è ancora un bootstrap leaf
onesto; l'altra metà è un driver di stampante.

### `env.inc` (763) — 7 responsabilità, 5 delle quali non c'entra niente con "env"

| Linee | Contenuto | Verdetto |
|---|---|---|
| 1-30 | `cassa_log()` + loader `.env` | **bootstrap reale** |
| 34-46 | `salsiccia_storage_*` | **bootstrap reale** |
| 52-75 | hardening sessione | **bootstrap reale** |
| 81-133 | throttle login su file | **sicurezza** |
| **139-158** | **gate stampante + `is_continuous`** | **stampante** |
| **166-279** | **mappa carta, normalizzazione ZPL/EPL, lettura setup** | **stampante** |
| **301-347** | **registry stampanti + selezione persistita** | **stampante** |
| **352-714** | **`lpstat -t/-p`, `lpinfo -v`, parser, USB, `printer_stato_locale_ok`** | **stampante / CUPS** |
| 722-762 | flag fiera su JSON | **config runtime** |

**~450 righe su 763 (59%) parlano di stampante.** Un file chiamato `env.inc` non dovrebbe
contenere un parser delle righe di `lpstat`. Non è un problema di dimensione, è di *nome
che mente*: chi apre `env.inc` per capire come è configurata la cassa trova 450 righe di
regex EPL.

### `funzioni.inc` (894) — 7 responsabilità

| Linee | Contenuto | Note |
|---|---|---|
| 7-9 | `ini_set` + `error_reporting` | side-effect **in un include**, colpisce anche i test |
| 12-29 | `csrf_*` | ok, 3 funzioni |
| 37-82 | `mysql_query_safe` / `db_select` / `db_exec` | 9 call site di raw SQL |
| 84-88 | `printA()` | **morta** (0 call site) |
| 90-172 | `calcolaTotali()` | **logica soldi**, con transazione — dovrebbe stare in `OrderService` |
| 174-892 | `testo_biglietti`, `stampaMenu`, `generaCardDegustazione`, `etichetta_continua`, `get_product_label` | builder etichette, ~700 righe |
| 412-447 | `printOre()` | **morta** |
| 448-458 | `stampa_contatori()` | **morta** |
| 459-508 | `conta_prodotto()` | **morta** (orfana dal collasso N+1 di T25) |

**~100 righe di codice morto**, verificato per call site su tutto il repo.

### `functionsFrontend.inc` (1588) — 9 cluster, 6 domini diversi

| Linee | Cluster | Dominio |
|---|---|---|
| 5-70 | side-effect di include (sessione, `$cat`, dispatch) | bootstrap |
| 137-346 | 11 `cassa_azione_*` + `gestisciAzioni` | HTTP |
| 349-653 | 15 `mostra*` catalogo/ordine/carrello | view cassa |
| 657-732 | pannello admin + tastierino config | view admin |
| **739-963** | **`sudo`/`systemctl`/`/proc/uptime`, marker, diagnostica** | **sistema Debian** |
| 967-1019 | contatori | view |
| 1023-1072 | `SHOW TABLES` + `REPAIR TABLE` | **DB admin** |
| 1076-1146 | **riscrittura di `set.inc`** | **config** |
| 1152-1380 | carta stampa + switch stampante + `lpstat` | **stampante** |
| 1386-1588 | stampa, chiusura ordine, scontrino fiscale, layout resto | **use case + view** |

Tre dei dieci cluster (`Power`, `Printer`, `DbRepair`) **non hanno nulla a che fare con una
cassa**: sono integrazione con il sistema operativo Debian. Stanno in un file chiamato
"frontend".

### Codice legacy — dove si vede

1. **`set.inc` metà morto**: `MENU_ENABLE`, `REPORT_CUCINA`, `REPORT_CUCINA_ROW`,
   `TOTAL_LABEL`, `ONE_LABEL_PER_PRODUCT`, `MODALITA_FIERA`, `DATA_CONFRONTO` — i commenti
   del file stesso dicono «riservata, nessun effetto attuale», «la costante non e' letta
   dal codice».
2. **`$remoteIP = $_SERVER['REMOTE_ADDR']` → `ID_CASSA`** (`set.inc:113-118`): più
   `REMOTE_CLIENT_IP` hardcoded a `192.168.0.30`. Fallback già superato da
   `cassaCorrente()`.
3. **Layout a costanti con fallback** (`BOT_X_ROW` 6/7 in base a `ONLY_ONE_CATEGORY`):
   geometria schermata modellata come costante PHP, mentre `CONTEXT.md` la descrive come
   legge di scala con clamp fluido. Divergenza tra documentazione e codice.
4. **`mysql_query_safe`**: nome che promette sicurezza e fa `mysqli_query` grezzo. 9 call
   site, tutte query statiche, quindi oggi è innocuo — ma il nome è un avviso per il
   prossimo.

### Duplicazione rilevata

- `printer_cups_queue()` sta in `env.inc:384` ma è usata da `functionsFrontend.inc:837` e
  `:1243` — una funzione di stampante che non sta nel modulo stampante.
- La lista schermate è in tre posti (§3.2a).
- Il guard `isAdmin() + redirect` è in 5 funzioni.
- `routes/cassa.php` caricato 2 volte.
- `cassa_azione_fiera:209` è **l'unico handler che non richiede POST** per una mutazione
  (`impostaModalitaFiera` scrive il flag). Tutti gli altri hanno `$post_ok`. È un GET che
  scrive su disco.

---

## 6. `.inc` — Piano di modularizzazione

**Criterio, non dimensione.** Le dimensioni seguono; non le guidano. Il criterio è:

> **Un file = un motivo per cambiare.**
> Se per cambiare la schermina contatori devo aprire anche il file che parla di `sudo`,
> i due non appartengono allo stesso file.
>
> Sotto questo, la domanda operativa è: *quando arriva la prossima fiera, quale file
> apro?* La risposta deve essere una. Oggi, per qualunque cosa tranne le etichette, la
> risposta è «`functionsFrontend.inc`».

### Mappa concreta

#### `functionsFrontend.inc` (1588)

```text
5-70    side-effect di include          → bootstrap.php  +  CassaController::gestisci()
137-346 cassa_azione_* + gestisciAzioni → src/Cassa/CassaController.php
349-653 viste catalogo/ordine/carrello  → src/Cassa/CassaView.php
657-732 pannello admin + config         → src/Cassa/AdminView.php
739-910 sudo/systemctl/marker/uptime    → src/System/Power.php      ◄ NUOVO DOMINIO
912-963 mostraPower/Riscontro/Diagnost. → 3 righe HTML in AdminView
967-1019 vista contatori                → src/Stats/AdminView.php
1023-1056 SHOW TABLES + REPAIR TABLE    → src/System/DbRepair.php  ◄ NUOVO DOMINIO
1056-1072 form repair                   → AdminView::repair()
1076-1127 lettura/riscrittura set.inc   → src/Config/FestaConfig.php (storage/festa.json)
1132-1146 form info festa               → AdminView::info()
1152-1213 toggle carta stampa           → src/Printer/PaperSetup.php + AdminView
1214-1247 selezione stampante JSON      → src/Printer/PrinterConfig.php
1256-1380 UI switch stampante           → src/Cassa/AdminView::switchPrinter()
1386-1479 layout resto (banconote)      → src/Cassa/StampaView.php
1482-1588 stampa + chiusura + fiscale   → src/Cassa/StampaController.php  ◄ SEPARAZIONE CHIAVE
```

#### `funzioni.inc` (894)

```text
7-9     ini_set/error_reporting    → ELIMINARE (php.ini su Debian, o ErrorHandler)
12-29   csrf_token/ok/field        → src/Support/helpers.php (autoload.files)
37-82   mysql_query_safe/db_*      → src/Support/Db.php
84-88   printA                     → ELIMINARE (morta)
90-172  calcolaTotali              → OrderService::calcolaTotali()
174-277 testo_biglietti            → src/Cassa/LabelBuilder.php
278-411 stampaMenu                 → src/Cassa/LabelBuilder.php  (VIVO: PrintService.php:129)
412-447 printOre                   → ELIMINARE (morta)
448-458 stampa_contatori           → ELIMINARE (morta)
459-508 conta_prodotto             → ELIMINARE (morta)
509-519 contatori_totali           → src/Stats/StatsData.php
520-752 generaCardDegustazione     → src/Cassa/LabelBuilder.php
596-752 etichetta_continua         → src/Cassa/LabelBuilder.php  (già coperto da PureBuildersTest)
753-894 get_product_label          → src/Cassa/LabelBuilder.php
```

#### `env.inc` (763)

```text
1-30    cassa_log + loader .env    → src/Support/Env.php
34-46   salsiccia_storage_*        → src/Support/Storage.php
52-75   session hardening          → src/Support/Session.php
81-133  throttle login             → src/Support/Throttle.php
139-158 gate + is_continuous       → src/Printer/PrinterRegistry.php
166-279 carta stampa               → src/Printer/PaperSetup.php
281-347 cutter/registry/selezione  → src/Printer/PrinterRegistry.php
352-714 lpstat/lpinfo/parser/USB   → src/Printer/CupsState.php
722-762 flag fiera                 → src/Config/CassaFlags.php
```

#### `set.inc` (150)

```text
costanti vive (BOT_X_*, ID_CASSA, ZPL_DOTS_PER_MM, ORA_CAMBIO, TIPO_ORDINE) → config/cassa.php
costanti morte (MENU_ENABLE, REPORT_CUCINA*, TOTAL_LABEL, ONE_LABEL_PER_*)  → ELIMINARE
$remoteIP/ID_CASSA sniffing                                                  → ELIMINARE (sessione basta)
EVENT_NAME / DURATA_FESTA (oggi riscritti su file!)                          → storage/festa.json
fail-closed SALSICCIA_PRINTER_IP / ADMIN_PWD_HASH                            → resta dove va (env)
```

**`set.inc` sparisce.** Non perché è grande, ma perché è *codice eseguibile che
l'applicazione riscrive*: finché `mostraModificaInfo` lo modifica, quel file non è
configurazione, è stato mutabile su una macchina condivisa da più persone.

---

## 7. `.inc` vs `.php`

### Verdetto sull'estensione

**`.inc` non ha più alcuna giustificazione tecnica.** Non è un problema, è una reliquia.

- **PSR-4 impone `.php`** (php-fig.org/psr/psr-4): *"The terminating class name corresponds
  to a file name ending in `.php`"*.
- Per il codice procedurale, Composer ha il meccanismo ufficiale giusto:
  **`autoload.files`**, pensato esattamente per "bootstrap code like global functions" che
  non sono classi. È supportato da sempre, non è una deprecazione.
- `.inc` era la convenzione dell'era pre-`require_once`-razionalizzato, quando serviva
  distinguere a occhio "questo file non va eseguito dal web". Da quando il docroot è
  `public/`, quel controllo lo fa Apache, non l'estensione.

L'argomento "rinominare rompe 15 require site" è vero ma è un argomento di **sequenza**,
non di merito. La risposta corretta non è «tieni `.inc` per non rompere niente», è:
**`.inc` sparisce perché il file sparisce**, non perché qualcuno ha fatto un rename. Chi
estingue `env.inc` non deve fare un rename: deve cancellare il file dopo aver spostato
le funzioni.

### Quando funzioni, quando classi

Non tutto deve diventare classe. La regola proposta, applicata a questo codice:

| Caso | Modello | Esempi reali nel progetto |
|---|---|---|
| Ha stato o una dipendenza | **classe** | `OrderService($db, $idCassa)`, `CatalogRepo($db)`, `PrintService` |
| Funzione pura, calcola e ritorna | **funzione** | `testo_biglietti()`, `etichetta_continua()`, `coloreCategoriaHex()` |
| Tocca superglobali, nessuno stato | **funzione** | `csrf_token()`, `csrf_ok()`, `csrf_field()` |
| Emette HTML da dati già pronti | **renderer statico** | `CatalogView::tabellaProdotti()` (precedente già in casa) |
| Integrazione hardware/OS | **classe con metodi statici** | `CupsState::stato()`, `Power::schedula()` |

`CatalogView` (statico, riceve dati già calcolati, zero query) è **già il modello giusto**
ed è già nel repo. La proposta lo estende, non lo inventa.

### Namespace e autoloading

Già impostati e funzionanti (`composer.json` → `"Salsiccia\\": "src/"`). Servono solo due
aggiunte:

- `autoload.files: ["src/Support/helpers.php"]` per le funzioni globali stateless che PHP
  non può autoloadare
- i nuovi file in `src/<Dominio>/` con `namespace Salsiccia\<Dominio>;`

### Cosa **non** fare

- **Niente DI container.** I servizi sono 3-4 oggetti (`$db`, `$id_cassa`, i config). Un
  container aggiunge un file, una classe e un livello di magia per zero guadagno su 3
  dipendenze. Constructor injection a mano, come `OrderService` e `CatalogRepo` già
  fanno.
- **Niente interfacce con una sola implementazione.** `PrintService` è statico e funziona:
  non gli serve un'interfaccia.
- **Niente template engine.** Le viste sono HTML inline con dati scalari. Un `.twig` per
  `mostraFooterBottoni` aggiunge un layer di compilazione su un kiosk Debian per un
  `<form>`. Se un domani un grafico deve poter modificare l'HTML, *allora* servono
  template — non prima.
- **Niente `declare(strict_types=1)` retroattivo ovunque.** I file nuovi sì. Quelli vecchi
  no, per non fare un reformat da 3000 righe in un commit.

---

## 8. Ricerca online

### Front controller: cosa dicono davvero Laravel e Symfony

**Laravel** (`public/index.php`, 24 righe, riprodotto per intero dalla Factory Laravel
wiki):

```php
define('LARAVEL_START', microtime(true));
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) { require $maintenance; }
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->handleRequest(Request::capture());
```

Quattro cose: timestamp, autoload, bootstrap, dispatch. **Né routing, né authz, né
HTML.** Tutto il resto vive in `bootstrap/app.php` e nei controller.

**Symfony** (*Front Controllers and Kernel*, symfony.com/doc/current/configuration/
front_controllers_and_kernel.html): *"The main purpose of the front controller is to create
an instance of the Kernel, make it handle the request and return the resulting response."*
Gli esempi di global initialization sono tre: autoloader, HTTP cache, debug.

**Il tutorial front-controller di Symfony** (symfony.com/doc/current/create_framework/
front_controller.html) è la fonte più utile per noi, perché costruisce il pattern da
zero: `front.php` fa autoload → `Request::createFromGlobals()` → mappa URL→file →
dispatch → `Response::send()`. Nota la frase chiave: *"we have indeed moved most of the
shared code into a central place, but it does not feel like a good abstraction... we are
still not able to test this code properly"*. Poi la risposta è: wrappa in una classe
`Framework` con un `handle(Request): Response`. **~100 righe, zero dipendenze.**

**PHP From Zero — Front Controller Pattern** (phpfromzero.com/29-design-patterns-and-
data-architecture/02-front-controller-pattern/) è la formulazione più diretta della
nostra situazione:

> *"It should not contain business logic. If `public/index.php` knows how to cancel
> subscriptions, calculate discounts, or query reports, the application boundary has
> leaked."*
>
> *"The application should also apply authentication and authorization in the request
> pipeline or controller layer, not in scattered public scripts."*

Entrambe le frasi descrivono esattamente i difetti P3 e P2(b) trovati sopra.

### Controller: quanto devono essere magri

**Symfony Best Practices** (symfony.com/doc/current/best_practices.html): *"Controllers
should contain nothing more than a few lines of glue-code, so you're not coupling the
important parts of your application."* E il criterio di divisione: *"Don't create bundles
to organize your own internal application logic: use PHP namespaces (under the `App\`
namespace)"* — cioè **namespace, non framework**.

Il pattern a strati più esplicito che ho trovato (github.com/fatonh/symfony-skills,
`layered-architecture`) è la specificazione più utile per il progetto:

```
Controller  ← solo HTTP, mai logica, mai repository
Service     ← tutta la logica, possiede la transazione
Repository  ← solo accesso dati
```

E la regola operativa che conferma la proposta: *"One service per aggregate"*, *"never
inject a repository into a controller — go through a service"*.

### `.inc`, autoloading e codice procedurale

- PSR-4 (php-fig.org/psr/psr-4/): `.php` obbligatorio per le classi.
- Sulla domanda "posso caricare funzioni con Composer?": la risposta dei maintainer
  (r/PHP, `composerjson_using_autoload_files_instead_of_psr4`) — *"If you have bootstrap
  code like global functions or non-psr4 classes you need loaded, you use `file`, but you
  still need to include the `autoload.php`."* **Il meccanismo ufficiale esiste e non è
  una deprecazione.**
- Nessuna sorgente consultata raccomanda `.inc` per codice nuovo.

### Sintesi: cosa è trasferibile e cosa no

| Principio | Applicabile qui? |
|---|---|
| Un solo file pubblico, `public/` come docroot | ✅ **Già fatto** (T16) |
| Front controller = bootstrap + dispatch, nient'altro | ✅ **Il punto 1 di questo intervento** |
| Controller = glue, niente logica, niente SQL | ✅ **Il punto 2 (separare `StampaController`)** |
| Routing in tabella dichiarativa, non `if`-chain | ✅ **Già fatto** (T23), va esteso a `view`+`auth` |
| Service possiede la transazione | ⚠️ `calcolaTotali` ha già `$in_txn`; va dentro `OrderService` |
| DI container | ❌ 3 dipendenze, un container è overhead |
| Bundle / plugin architetturale | ❌ YAGNI assoluto |
| Template engine | ❌ viste con dati scalari, non lo giustifica |
| PSR-4 + `autoload.files` | ✅ **Già a metà, si completa** |
| Composer | ✅ **Già fatto** |

---

## 9. Struttura target

```text
STRUTTURA ATTUALE                          STRUTTURA PROPOSTA
─────────────────                          ───────────────────

public/index.php        134 righe    →    public/index.php   8 righe
  (12 boolean, 3 liste)                      (bootstrap + 2 chiamate)

                            public/index.php (invariato)
functionsFrontend.inc  1588 righe   →    src/Cassa/CassaController.php   azioni HTTP
  ├ 5-70   side-effect                      src/Cassa/CassaView.php       viste ordine
  ├ 137-346 azioni                          src/Cassa/StampaController.php stampa+chiusura
  ├ 349-653 viste                           src/Cassa/StampaView.php      layout resto
  ├ 657-732 admin                           src/Cassa/AdminView.php       pannello+tastierino
  ├ 739-963 sudo/systemd          ───┐      src/Cassa/LabelBuilder.php   etichette
  ├ 967-1019 contatori                  │     src/Printer/PrinterRegistry.php
  ├ 1023-1056 REPAIR TABLE        ───┤     src/Printer/PaperSetup.php
  ├ 1076-1146 set.inc rewrite    ───┤     src/Printer/CupsState.php
  ├ 1152-1380 stampante          ───┤     src/Printer/PrinterConfig.php
  └ 1386-1588 stampa+fiscale    ───┘     src/System/Power.php     ◄ DOMINIO NUOVO
                                          src/System/DbRepair.php  ◄ DOMINIO NUOVO
                                          src/Config/CassaConfig.php
                                          src/Config/FestaConfig.php  ◄ storage/festa.json
                                          src/Config/CassaFlags.php

funzioni.inc            894 righe   →    src/Support/Db.php          connect/select/exec
  ├ 7-9    ini_set                  →    src/Support/helpers.php     csrf_* (autoload.files)
  ├ 12-29  csrf_*                   →    src/Cassa/OrderService.php  + calcolaTotali()
  ├ 37-82  db_*                     →    src/Stats/StatsData.php     + contatori_totali()
  ├ 84-88  printA          MORTA     →    ── eliminata
  ├ 90-172 calcolaTotali             →    src/Cassa/LabelBuilder.php  testo_biglietti…
  ├ 412-447 printOre        MORTA     →    ── eliminata
  ├ 448-458 stampa_contatori MORTA   →    ── eliminata
  └ 459-508 conta_prodotto  MORTA   →    ── eliminata
  └ 174-892 builder etichette

env.inc                 763 righe   →    src/Support/Env.php         .env + cassa_log
  ├ 1-46   bootstrap                         src/Support/Storage.php
  ├ 52-133 session+throttle                  src/Support/Session.php
  └ 139-714 STAMPANTE (59%)                  src/Support/Throttle.php

set.inc                 150 righe   →    config/cassa.php  (costanti vive)
  ├ costanti morte (7)                       storage/festa.json (EVENT_NAME, DURATA)
  ├ $remoteIP sniffing                       ── eliminato
  └ fail-closed env          invariato       fail-closed env     invariato

dbConnect.php            19 righe   →    src/Support/Db::connetti()

routes/cassa.php          41 righe  →    routes/cassa.php  ESTESO: handler|view|auth
                                             (1 riga per schermata, 1 fonte di verità)

─────────────────────────────────────────────────────────────────────────────
config/cassa.php  ·  database/migrations/  ·  public/reserved/  ·  src/Catalog
src/Fiscale  ·  src/Stats  ·  src/Backoffice  ·  src/Backup  ·  src/Support/ErrorHandler
tests/  ·  storage/  ·  resources/
                          ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓ ↓
ELIMINABILI: env.inc · functionsFrontend.inc · funzioni.inc · set.inc · dbConnect.php
              (−3395 righe, −5 file, −5 header "T31 rivalutare entro 2026-12-31")
```

**Numeri:** 5 file legacy in 3395 righe → 0. `functionsFrontend.inc` (1588) diventa 5
file da 100-350 righe, ognuno con un nome che dice cosa contiene. `env.inc` (763, 59%
stampante) diventa 4 file `Printer/` da 100-200 righe.

---

## 10. Roadmap concettuale

Ordine scelto per **rischio crescente**. Ogni fase è committabile e rollbackabile da sola.

### Fase 0 — Zero rischio, alto rendimento (mezza giornata)

1. **Cancellare 4 funzioni morte** (`printA`, `printOre`, `stampa_contatori`,
   `conta_prodotto`). Verificato 0 call site. −100 righe.
2. **Togliere `ini_set`/`error_reporting` da `funzioni.inc:7-9`**, mettendoli in
   `ErrorHandler::registra()`. Oggi un include di un file di funzioni cambia gli `ini_set`
   globali del processo, anche nei test.
3. **Fix P4: `storage/festa.json`.** Non è cosmetica, è un bug: una scrittura non atomica su
   sorgente PHP su tre macchine condivise. Stessa soluzione già usata per
   `MODALITA_FIERA` (T17), quindi il pattern è in casa.

> **Payoff immediato:** ~100 righe morte eliminate, il rischio di schermata bianca
> post-fiera rimosso, bootstrap più pulito. Nessun file di production spostato.

### Fase 1 — `index.php` onesto (1 giorno)

4. Estendere `routes/cassa.php` a `['handler'=>…, 'view'=>…, 'auth'=>…]`.
5. `CassaController::gestisci($db)` in `src/Cassa/` — assorbe `gestisciAzioni()` + i 12
   boolean + la lista admin.
6. `CassaView::render($view, $db)` — `match` sulle schermate + shell `<html>`.
7. `index.php` → 8 righe.

> **Payoff:** le tre copie del routing diventano una. Aggiungere una schermata = una
> riga. Il diff più piccolo e più verificabile di tutta l'iniziativa, perché `index.php`
> non ha logica: si può rileggere in 10 secondi.

### Fase 2 — Le viste che scrivono (2-3 giorni)

8. `StampaController::stampa()` — ordina, stampa, chiude, reindirizza. `StampaView` per il
   resto.
9. `DbRepair::run($db)` + form in `AdminView`.
10. `PrinterConfig::salva()` (già esiste come `impostaStampanteSelezionata`, `:1214`).
11. `AdminView` — pannello, tastierino, contatori, power, info, carta, stampante.

> **Payoff:** il nome `mostra*()` smette di mentire. Le scritture hanno un nome, un posto,
> e sono testabili. È il fix più importante per la manutenzione.

### Fase 3 — `bootstrap.php` e la fine dell'include con effetti (mezza giornata)

12. `bootstrap.php` in root; `functionsFrontend.inc` diventa richiedibile senza aprire il
    DB.
13. **`PowerF2Test` smette di usare `eval()`** — il test delinea il confine, non lo aggira.

> **Payoff:** ~15 `require_once` manuali diventano 1 riga in 5 entry point. Il test più
> scomodo del repo sparisce.

### Fase 4 — `funzioni.inc` (1-2 giorni)

14. `src/Support/Db.php` + `helpers.php` (via `autoload.files`).
15. `OrderService::calcolaTotali()`, `StatsData::contatori_totali()`.
16. `LabelBuilder` con test **prima** dello spostamento (`testo_biglietti`,
    `get_product_label` — i due builder più money-critical ancora senza test).
17. `funzioni.inc` → shim di una riga → **delete**.

> **Payoff:** i calcoli soldi hanno una casa sola. `testo_biglietti` (wrapping righe
> scontrino) e `get_product_label` (riga etichetta) diventano testabili — oggi non lo
> sono.

### Fase 5 — `env.inc` (1-2 giorni)

18. `src/Printer/*` (4 file) — la parte che oggi è il 59% di un file chiamato `env`.
19. `src/Support/{Env,Storage,Session,Throttle}.php`.
20. `env.inc` → shim → **delete**.

> **Payoff:** `env.inc` era il file più richiesto del progetto (10 consumer). È anche
> quello con la responsabilità più sbagliata. Separarlo libera il resto.

### Fase 6 — `set.inc` (mezza giornata)

21. Costanti vive → `config/cassa.php`. 7 costanti morte → eliminate. `$remoteIP` →
    eliminato.
22. `set.inc` → **delete**.

> **Payoff:** nessun file di configurazione è più sorgente eseguibile. Un config file non
> dovrebbe poter essere riscritto da una schermata admin.

### Fase 7 — Igiene (ongoing)

23. Aggiungere `phpcs` sui nuovi file senza rimuovere i `grandfather` di `phpcs.xml` (già
    la politica T29: normalizza i file nuovi, non rifare i vecchi).
24. `cassa_azione_fiera` (`functionsFrontend.inc:209`): l'unico handler che muta senza
    POST. Allinearlo agli altri 10.

---

## 11. Conclusione

### Domanda 1 — Come risolvere il problema di `index.php`

**La preoccupazione va corretta.** `index.php` non ha troppa logica applicativa: 134
righe, zero SQL, zero decisioni di dominio. Il refactor T23 ha già fatto il grosso del
lavoro. Sarebbe un errore ridisegnarlo da zero.

Il problema reale è che **`index.php` non è il vero entry point**, e questo produce tre
difetti misurabili:

1. **Il routing è in tre copie** (`index.php:12-24`, `:26`, `:36`) più una quarta in
   `routes/cassa.php`. Aggiungere una schermata richiede 3 edit manuali; sbagliarne uno
   non dà un errore, dà uno sfondo sbagliato o un pannello admin aperto a chiunque.
2. **Le "viste" scrivono.** `mostraSchermataStampa()` stampa, chiude l'ordine nel DB e
   emette lo scontrino fiscale. Finché `index.php:60` chiama una vista che ha effetti
   monetari, `index.php` non può essere un semplice dispatcher.
3. **Le dipendenze sono un contratto implicito.** `$mysqli`, `$cat`, `$but_x_row`,
   `$but_x_col` nascono come effetto collaterale di un `require` e vengono consumati 80
   righe più in basso.

**La soluzione è di 3 file, non di una riscrittura:**

- `routes/cassa.php` diventa l'unica fonte di verità:
  `['handler' => …, 'view' => …, 'auth' => …]`. Spariscono i 12 boolean, la catena di 12
  `&&` negati e la lista admin hardcoded. **Aggiungere una schermata = una riga.**
- `CassaController::gestisci($db)` risolve action, autorizza, esegue la mutazione,
  restituisce il nome della schermata. È l'unico posto dove una richiesta viene
  trasformata in azione.
- `CassaView::render($view, $db)` riceve un nome **già validato e autorizzato** e produce
  HTML. Non vede `$_GET`. Non può scrivere.

`index.php` diventa 8 righe, e le responsabilità che oggi sono invisibili dentro un
`require` diventano tre righe ciascuna, in fila, leggibili.

**Il principio di riferimento**, verificato online (Symfony, Laravel, PHP From Zero):
bootstrap, dispatch, risposta — nient'altro. *"If public/index.php knows how to …
calculate discounts, or query reports, the application boundary has leaked."* Oggi la
boundary è ruvata perché `mostraSchermataStampa` emette documenti fiscali.

### Domanda 2 — Come risolvere i `.inc` enormi

**Sì, vanno divisi** — ma **non perché sono grandi**, e **non con un criterio di
dimensione**.

Il criterio è: **ogni file ha un solo motivo per cambiare, e alla prossima fiera sai quale
file aprire.** Oggi, per tutto tranne le etichette e la connessione DB, la risposta è
«`functionsFrontend.inc`» — 1588 righe che vanno dal routing HTTP al probing CUPS con
`lpinfo`.

Il vero indicatore di accretione non è la lunghezza: è che **`env.inc` contiene più codice
di stampante che di configurazione** (450 righe su 763, 59%), e **`functionsFrontend.inc`
contiene tre domini che non sono cassa** — `sudo`/`systemctl`/`/proc/uptime` (righe
739-963), `REPAIR TABLE` (1023-1056), `lpstat` (1256-1380).

**Sì, più file — e per il 40% possono essere normali `.php` con classi**, perché la
maggior parte del codice ha già una forma migliore in `src/`:

- **`src/Printer/`** (4 file) — il dominio più grande e meglio definito del legacy, e il
  peggio piazzato.
- **`src/System/Power.php`** — reboot/poweroff con `sudo` e marker. È integrazione Debian,
  non frontend.
- **`src/Support/Db.php` + `helpers.php`** — CSRF e accesso dati hanno una dipendenza,
  quindi una classe; `csrf_*` no, quindi funzioni via `autoload.files`.
- **`src/Cassa/CassaView.php`** — segue il precedente `CatalogView` **già presente in
  casa**: statico, dati già calcolati, zero query.

**Cosa NON diventa classe:** i builder puri (`testo_biglietti`, `etichetta_continua`,
`get_product_label`), i renderer HTML, e `csrf_*`. Restano funzioni. Una classe per ognuno
sarebbe architettura cosmetica, non struttura.

**Su `.inc`:** non ha più giustificazione tecnica. PSR-4 impone `.php` per le classi; per
le funzioni procedurali Composer ha `autoload.files`, pensato esattamente per quello. Ma
la cosa giusta **non è rinominare i file**: è che `.inc` scompare *perché il file è stato
svuotato e cancellato*. 5 file legacy, 3395 righe, spariscono dalla root; nessun `git mv`
di comfort.

**Il risultato** è che il progetto passa da «un file da 1588 righe che contiene tutto» a
una struttura dove il nome della directory ti dice cosa ci sta dentro, e — dato che gira
su casse Debian senza rete e con `systemd`/CUPS/`sudo` — resta **zero framework, zero DI
container, zero template engine**. Solo namespace, PSR-4 già configurato, e classi solo
dove c'è stato o dipendenza.
