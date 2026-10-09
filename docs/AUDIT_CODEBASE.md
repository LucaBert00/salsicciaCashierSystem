# AUDIT CODEBASE — salsicciaCashierSystem

Audit architetturale e di qualità del codice, condotto in sola lettura.
Data: 2026-10-09 · Branch: `master` · Commit: `fc93cd7`
Target primario: `functionsFrontend.inc`

> **Stato del working tree al momento dell'audit** (preservato, non toccato):
> `public/reserved/statistiche.php`, `public/reserved/visualizza_view.php`, `public/style.css`
> con modifiche non committate. L'audit non ha creato, modificato, cancellato o spostato
> alcun file, e non ha eseguito operazioni Git che alterassero working tree, indice o cronologia.

---

## A. Executive Summary

Questo non è un legacy da recuperare. È un **codice a migrazione di mezzo, con la migrazione
quasi finita**, dove il problema strutturale residuo è la *sporca della migrazione stessa*,
non il progetto originale.

Fatti verificati:

- **Lint:** 0 errori di sintassi su tutti i 76 file PHP/INC non-vendor (`php -l`, PHP 8.2.12).
- **Test:** 149 test, 454 asserzioni, **tutti verdi** (2 warning PHPUnit) — `vendor/bin/phpunit`.
- **Analisi statica:** PHPCS PSR-12 → **0 errori, 265 warning** su 30 file. Tutti i file legacy
  sono esplicitamente grandfathered in `phpcs.xml:17-26`.
- **Zero percorsi di SQL injection.** Ogni query che porta input utente usa prepared statement
  con parametri bound. I due helper raw (`Db::query`, `Db::exec`) ricevono solo SQL statico:
  verificati tutti i call site in `src/Catalog/CatalogRepo.php`, `src/Stats/StatsData.php`,
  `src/System/DbRepair.php`, `src/Backup/BackupRun.php`.
- **CSRF** implementato correttamente come synchronizer token (`src/Support/helpers.php:12-37`),
  con `hash_equals`, legato alla sessione, rigenerato all'auth. Ogni handler mutante richiede
  POST + token.
- **Password** in Argon2id, verificate via `password_verify`, con `session_regenerate_id(true)`
  su entrambi i path di auth e throttle persistente su file (`src/Support/Throttle.php`) che
  sopravvive al reset della sessione.

I reperti reali:

1. **`functionsFrontend.inc` è di 875 righe, ma ~171 (19,5%) sono codice morto** — 17 funzioni
   con zero chiamanti in tutto il repository, verificate con una scansione esaustiva del call
   graph su tutti i file non-vendor con i commenti rimossi. È l'elemento azionabile più grande
   ed è * pura cancellazione*, non refactoring.
2. **Un difetto reale di correttezza/sicurezza**: `src/Cassa/StampaController.php:95-119` legge
   `id_ordine` da `$_GET['stampato']` **senza scope `id_cassa`**, poi emette lo scontrino
   fiscale per quell'ordine. GET non autenticato → emissione di documento fiscale per un
   id_ordine arbitrario.
3. **Denaro memorizzato e calcolato in `float`** end-to-end
   (`database/migrations/0001_schema.sql:52,77`; `OrderService::calcolaTotali` a
   `src/Cassa/OrderService.php:220`).
4. **Il codice vivo residuo in `functionsFrontend.inc` è coeso.** ~700 righe vive, e la parte
   viva è essenzialmente: (a) 11 adapter HTTP sottili su `OrderService`, (b) 14 emitter di
   vista su `CatalogView`/`AdminView`, (c) 8 helper critici/puri. È una forma difendibile.

**Bottom line sulla domanda principale:** la lunghezza di ~800 righe di `functionsFrontend.inc`
è **sospetta ma non è il problema reale**. Il problema reale è che il 19,5% è irraggiungibile.
Dopo la cancellazione del codice morto il file si assesta su ~700 righe con una responsabilità
unica e coerente ("adapter di richiesta cassa + emitter di vista"). **Non raccomando di
spezzarlo ulteriormente.** Vedi §C per il verdetto completo.

---

## B. Repository and Architecture Overview

### Tecnologia (tutto verificato dal repository)

| Elemento | Valore | Evidenza |
|---|---|---|
| Linguaggio target | PHP `^8.2` | `composer.json:7` |
| Framework | **Nessuno** | niente symfony/laravel; PHP nativo |
| Composer | **Presente**, usato solo come autoloader | `composer.json:14-21` |
| Dipendenze runtime | `setasign/fpdf` 1.9.0 | `composer.lock` |
| Dipendenze dev | phpunit 11.5, php_codesniffer 3.10 | `composer.json:10-13` |
| DB engine | MariaDB 10.4 (laptop di fiera) | `database/migrations/0001_schema.sql:4` |
| Accesso | `mysqli` procedurale + OO misti, no ORM, no PDO | `dbConnect.php:22`, `src/Support/Db.php` |
| Schema | 8 tabelle, migrazione MyISAM→InnoDB + FK in `0003_innodb.sql` | `database/migrations/` |
| Test | PHPUnit, 149 test, suite Unit + Integration | `phpunit.xml` |
| Docroot | `public/` | `public/.htaccess:2`, `.htaccess:2` |
| Storage | `storage/` fuori docroot | `storage/.htaccess`, `.htaccess:2` |

### Entry point (6 totali, tutti richiedono `bootstrap.php`)

| Entry | Scopo |
|---|---|
| `public/index.php` | front controller cassa (38 righe) |
| `public/reserved/login.php` | auth backoffice |
| `public/reserved/visualizza.php` | CRUD backoffice 5 tab |
| `public/reserved/statistiche.php` | statistiche giornata fiscale |
| `public/reserved/stat_pdf.php` | export PDF |
| `public/reserved/backup.php` | dump DB fine giornata |

### Catena di inizializzazione — `bootstrap.php` (31 righe)

Ordine di boot singolo, esplicito, leggibile:

```
vendor/autoload.php          → PSR-4 Salsiccia\ + helpers.php (autoload.files)
ErrorHandler::registra()     → handler eccezioni + shutdown, display_errors=0
Env::carica()                → loader .env, env di sistema vince
CassaConfig::carica()        → fail-closed se mancano ADMIN_PWD_HASH / PRINTER_IP
dbConnect.php                → fail-closed se mancano credenziali DB, imposta $mysqli
functionsFrontend.inc        → solo definizioni, zero side effect (F3.2 #95)
Session::start()             → HttpOnly + SameSite=Lax + use_strict_mode
CassaController.php          → richiesto a mano (filesystem case-sensitive)
CassaView.php
```

**Questo è buon design.** L'ordine di boot è documentato, il comportamento fail-closed è
esplicito, e non restano side effect a include-time nel file frontend. Qualunque problema
residuo non è un problema di bootstrap.

### Flusso di richiesta (verificato end-to-end)

```
GET /index.php?action=X&cat=N
  → bootstrap.php (tutto quanto sopra)
  → defaultCat($mysqli)                     [functionsFrontend.inc:35]
  → CassaConfig::carica() → BOT_X_ROW/COL   [public/index.php:16-18]
  → CassaController::gestisci($mysqli)      [src/Cassa/CassaController.php:21]
       legge routes/cassa.php                [routes/cassa.php] — fonte unica
       → auth gate via $riga['auth']=='admin'
       → $handler($db, $cassaCorrente())
       → ritorna il nome vista validato
  → CassaView::render($view, ...) [src/Cassa/CassaView.php:41]
       match($view) → metodi privati corpo*
       → chiama le funzioni mostra*() in functionsFrontend.inc
       → queste delegano a CatalogView / AdminView / StampaView
```

**La tabella di routing è realmente buon design.** `routes/cassa.php` è un array puro di 39
righe che mappa `action => {handler, view, auth}`. La vecchia catena `if/elseif` e le tre
whitelist separate sono sparite. Ogni handler ha la firma uniforme `($mysqli, $id_cassa)`,
quindi il dispatch è `function_exists` + call, mai un fatal. Non ho critiche su questo file.

### Mappa dei moduli

```
Salsiccia\
  Support/     Db, Env, ErrorHandler, Session, Storage, Throttle, helpers.php
  Config/      CassaConfig, CassaFlags, FestaConfig
  Printer/     PrinterRegistry, PaperSetup, PrinterConfig, CupsState
  Cassa/       CassaController, CassaView, AdminView, StampaView, StampaController,
               PrintService, LabelBuilder, OrderService, OrderType, PayMethod
  Catalog/     CatalogRepo, CatalogView
  Fiscale/     Fiscale
  Stats/       StatsData
  Backoffice/  VisualizzaStore, Tabs/*Tab.php (5)
  Backup/      BackupRun
  System/      Power, DbRepair
```

### Convenzioni di layering effettivamente seguite

- **Pattern Repo/Service** con `$db` iniettato via costruttore (un `mysqli` reale o una
  doppia di test con la stessa superficie): `OrderService`, `CatalogRepo`,
  `Fiscale::ritentaCoda($trasmetti)` con sender iniettabile.
- **Classi renderer statiche**, niente template engine, niente DI container:
  `CassaView`, `CatalogView`, `AdminView`, `StampaView`.
- **Enum per i set di valori chiusi**: `OrderType`, `PayMethod`.
- **Funzioni globali sottili in `helpers.php`** per le cose stateless che toccano le
  superglobali (`csrf_*`, `db_*`). Giustificato esplicitamente nell'header del file a
  `src/Support/helpers.php:5-11`, e il ragionamento è corretto: non sono affare di classi.

Questa è una **convenzione deliberata, coerente, documentata**. Il progetto ha deciso
esplicitamente contro interfacce con una sola implementazione e contro classi-wrapper attorno
a funzioni pure (`docs/ARCHITETTURA_REVISTA.md:485-530`). Quella decisione è corretta per
questa scala e non la metto in discussione.

---

## C. Detailed Analysis of `functionsFrontend.inc`

### C.0 Discrepanza sul path (disclosure richiesta)

Il task indicava il file **`function frontend.inc`**. Il file reale è:

```
C:\xampp\htdocs\salsicciaCashierSystem\functionsFrontend.inc
```

La discrepanza è di spaziatura tra parole, non di file mancante. Da notare:
`docs/ARCHITETTURA_REVISTA.md` registra questo file a **1588 righe** alla data della review,
con un header `T31` a riga 3 che impegna a *"rivalutare entro 2026-12-31"*. Oggi è **875
righe**. Il file è già stato dimezzato dalla migrazione F1–F7 completata. Tutte le misure
sotto sono sullo stato corrente di 875 righe, non sulle cifre obsolete del documento.

### C.1 Misure oggettive

Misurate con PowerShell sul file grezzo, commenti contati separatamente:

| Metrica | Valore |
|---|---|
| Righe fisiche totali | **875** |
| Righe vuote | 89 (10,2%) |
| Righe di commento (`//`, `*`, `/*`) | 157 (17,9%) |
| Righe di codice eseguibile | **629 (71,9%)** |
| Funzioni | **61** |
| Classi / interfacce / trait | 0 |
| Costanti / globali a top-level | 0 |
| Funzione più lunga | `mostraModificaOrdine` — 64 righe (righe 400-463) |
| Nidificazione massima | 4 livelli (loop righe `mostraModificaOrdine` → `$qta > 1` → form) |
| Cluster documentati | 3 (adapter HTTP, emitter vista, helper critici) |

**Questi numeri da soli non giustificherebbero alcun rilievo.** 61 funzioni con media di 14
righe, nessuna classe, nessuna globale, nessuna nidificazione profonda, nessuna duplicazione
di *logica*: per conteggio righe è un file ben comportato. Il problema è altrove.

### C.2 Il rilievo sul codice morto — 17 funzioni irraggiungibili

Ho eseguito una scansione esaustiva del call graph: per ognuna delle 61 funzioni ho contato i
riferimenti dentro `functionsFrontend.inc` e in tutti gli altri `.php`/`.inc` non-vendor, con
commenti e doc-block rimossi, più un passaggio separato per i riferimenti a stringa (necessario
perché `routes/cassa.php` fa dispatch per *nome* handler).

**Risultato: 17 funzioni, zero chiamanti ovunque.**

| Funzione | Righe | Corpo | Dove *dovrebbe* essere chiamata |
|---|---|---|---|
| `gestisciAzioni` | 312-330 | 18 righe — loader tabella dispatch | Superata da `CassaController::gestisci()` |
| `mostraRiscontroPower` | 724-727 | shim 3 righe | `AdminView::riscontroPower()` |
| `mostraDiagnosticaPower` | 730-733 | shim 3 righe | `AdminView::diagnosticaPower()` |
| `mostraPower` | 735-738 | shim 3 righe | `AdminView::power()` |
| `mostraRestart` | 740-743 | shim 3 righe | `AdminView::restart()` |
| `mostraShutdown` | 745-748 | shim 3 righe | `AdminView::shutdown()` |
| `impostaStampanteSelezionata` | 800-803 | shim 3 righe | `PrinterConfig::salva()` |
| `stampante_switch_ok` | 811-814 | shim 3 righe | `PrinterConfig::raggiungibile()` |
| `mostraLayoutResto` | 866-869 | shim 3 righe | `StampaView::resto()` |
| `mostraSchermataStampa` | 872-875 | shim 3 righe | `StampaController::stampa()` |
| `mostraContatori` | 755-758 | shim 3 righe | `AdminView::contatori()` |
| `mostraPannelloAdmin` | 656-659 | shim 3 righe | `AdminView::pannello()` |
| `mostraPannelloConfig` | 647-650 | shim 3 righe | `AdminView::tastierino()` |
| `mostraRipristinaDb` | 765-768 | shim 3 righe | `AdminView::repair()` |
| `mostraModificaInfo` | 776-779 | shim 3 righe | `AdminView::info()` |
| `mostraPrintReset` | 788-791 | shim 3 righe | `AdminView::carta()` |
| `mostraSwitchPrinter` | 859-862 | shim 3 righe | `AdminView::switchPrinter()` |

Inclusi i loro blocchi di commento (alcuni sono prolissi — `mostraSwitchPrinter` da sola porta
un commento di 8 righe **duplicato**, vedi §C.6): **171 righe, 19,5% del file.**

**Perché questo conta oltre al conteggio righe.** Ognuna di queste porta un commento che
asserisce essere uno shim di compatibilità obbligatorio:

```php
// F2.4 #91: unica implementazione vera in Salsiccia\Cassa\AdminView::tastierino()
// (src/Cassa/AdminView.php); qui resta solo lo shim con stessa firma.
// Delete legacy mai in F2 (§9).
```

`src/Cassa/CassaView.php` **non ne chiama nessuna** — chiama direttamente `AdminView::tastierino()`,
`AdminView::pannello()`, `AdminView::contatori()`, `AdminView::repair()`, `AdminView::carta()`,
`AdminView::switchPrinter()`, `AdminView::info()`, `AdminView::restart()`, `AdminView::shutdown()`
(`CassaView.php:110,153,161,169,177,185,193,201,118`). La migrazione è completata; la premessa
del blocco di commenti è superata.

Conseguenza: un maintainer che legge il file crede che 9 contratti di compatibilità siano vivi
e vadano preservati in ogni refactor futuro. Sei di questi sono già contratti spezzati —
`CassaViewTest.php:70-86` fa `eval()` di **stub su questi stessi nomi** quando sono assenti,
quindi la suite continuerebbe a passare indipendentemente dal fatto che i corpi veri esistano.

**Confermato dai test.** `tests/Unit/PowerF2Test.php:26` è l'unico test che fa `require` diretto
del file, ed esercita esattamente due delle funzioni morte (`esitoTentativoPower`,
`escapaComando`) — che *non* sono nella lista dei morti. Le altre 17 non sono testate e sono
irraggiungibili.

### C.3 Cosa fanno davvero le 700 righe vive

La parte viva ha esattamente tre cluster coerenti, in quest'ordine:

**Cluster A — adapter di richiesta HTTP (righe 120-330, 11 handler + dispatcher).**

Forma uniforme, deliberatamente uniforme:

```php
function cassa_azione_quantita($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok && isset($_GET['id']) && isset($_GET['qta']))
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->impostaQuantita((int)$_GET['id'], (int)$_GET['qta']);
        header("Location: ?action=m");
        exit;
    }
}
```

Cosa fanno questi 11: check POST, check CSRF, cast `(int)`, delega a `OrderService` o
`CatalogRepo`, redirect. **Quella è l'intera responsabilità.** Nessuna business rule, nessun
SQL, nessun HTML. È controller sottile da manuale e sono corrette.

`gestisciAzioni` (morta) era la versione precedente di questo stesso ruolo;
`CassaController::gestisci()` l'ha sostituita. Gli 11 handler restano vivi perché
`routes/cassa.php` fa dispatch per stringa.

**Cluster B — emitter di vista (righe 333-660, 14 funzioni).**

Due sotto-spezie, nettamente separate:

*Funzioni vista vere* (niente SQL, delegano a una View) — `mostraNavCategorie`, `mostraTitolo`,
`mostraTabellaProdotti`, `mostraBarraBarcode`. Sono 3-8 righe ciascuna e non fanno altro che
costruire un `CatalogRepo` e inoltrare a `CatalogView`:

```php
function mostraTitolo($mysqli, $cat)
{
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    \Salsiccia\Catalog\CatalogView::titolo($repo->descrizioneCategoria((int)$cat));
}
```

*Funzioni vista che portano SQL* (query + render) — `mostraIndicatoreTipo` (riga 347),
`mostraModificaOrdine` (riga 400), `mostraOrdiniStandby` (riga 514), `mostraBoxRiepilogo`
(riga 570), `mostraListaProdotti` (riga 605). Cinque funzioni, 15-64 righe ciascuna, ognuna
read-render per esattamente un frammento di schermata. Questo è **l'unico gruppo realmente a
responsabilità mista** ed è *migrazione incompleta*, non design.

**Cluster C — helper critici condivisi (righe 12-118, 107 righe).**

`isAdmin`, `cassaCorrente`, `defaultCat`, `isFieraAttiva`, `impostaModalitaFiera`,
`impostaCartaStampa`, `coloreCategoriaHex`, `tagliaVisibile`. Tutti piccoli, tutti stateless,
tutti con commenti sostanziali che spiegano *perché* il comportamento è quello che è. Questo
cluster è genuinamente buono — vedi §C.7.

### C.4 L'unico difetto reale a responsabilità mista

**`mostraModificaOrdine` (functionsFrontend.inc:400-463)** è la funzione più lunga del file e
l'unico punto in cui una query SQL consistente e HTML consistente coesistono nella *stessa*
funzione non migrata:

```php
function mostraModificaOrdine($mysqli)
{
    $id_cassa = cassaCorrente();
    $ris = db_select($mysqli, "SELECT prodotti.id_prodotto AS id, ... FROM prodotti, ordini, righe_ordini WHERE ... AND id_cassa = ? AND ordini.chiuso = 0", 'i', array($id_cassa));
    echo '<section class="modifica-section">';
    // ... 60 righe di tabella + form +/- inline + csrf_field() ...
}
```

Confronta con la sua sorella già migrata `mostraOrdiniStandby` (riga 514), che ha la forma
identica ma usa `mysql_query_safe` per il suo SQL statico. Entrambe sono one-query-one-screen.
Non sono logica duplicata — colonne diverse, azioni diverse — ma sono lo stesso *tipo* di
funzione che vive nello stesso file mentre le sue sorelle sono andate in `src/`.

Questo è **Low severity**. Sono 64 righe, è una singola operazione di business coerente
("renderizza il carrello editabile"), ed è corretto. Conta solo come cucitura non finita, non
come difetto.

### C.5 Duplicazione — verificata, in gran parte assente

Ho cercato specificamente business rule duplicate. Esito:

- **`mostraFooterModifica` (480) vs `mostraFooterBottoni` (559)** — quasi identiche: entrambe
  emettono lo stesso form ANNULLA, entrambe chiamano `tagliaVisibile()`, entrambe emettono un
  controllo STAMPA. Differiscono per un punto significativo: `modifica` usa `<a href>` per
  TAGLIA/STAMPA, `bottoni` usa `<button onclick="location.href=...">`. È una differenza di
  comportamento reale (semantica di navigazione), non copy-paste. **Non vale la pena
  consolidare** — unificare richiederebbe un parametro per una differenza booleana e
  accoppierebbe due schermate che cambiano indipendentemente.

- **`cassa_azione_barcode` (230) vs `mostraBarraBarcode` (377)** — entrambe fanno l'identico
  `substr(trim($_POST['bc']), 0, 20)` + check sentinella `'-'` + `CatalogRepo::trovaIdPerBarcode()`.
  Questa **è** duplicazione reale della regola di normalizzazione input. Però
  `CatalogRepo::trovaIdPerBarcode()` (`src/Catalog/CatalogRepo.php:96-99`) esegue lo *stesso*
  check trim/cap/`'-'` internamente, quindi la regola esiste in **tre** posti. La versione
  nell'handler è ridondante. **Low severity** — rimuovere i check duplicati nei due caller
  è una modifica di 4 righe con un test in `tests/Unit/ProdottiBarcodeTest.php` che copre già
  il comportamento a livello repo.

- **Formattazione del denaro** — `number_format($x, 2, ',', '.')` compare ~15 volte nel file e
  in `CatalogView`, `StampaView`, `AdminView`. Ho verificato ogni occorrenza: tutte formattano
  **euro per display italiano**. Non c'è caso in cui una formatta una percentuale o un conteggio
  allo stesso modo. **Non è duplicazione che vale un'estrazione** — un wrapper `fmtEuro()` sarebbe
  un'indirezione senza guadagno comportamentale.

- **`coloreCategoriaHex` vs `VisualizzaStore::mappaColori`** — esistono due vocaboli colore
  (`functionsFrontend.inc:94-109` hex→nome, `VisualizzaStore::mappaColori()` nome→codice). Sono
  inversi l'uno dell'altro, non duplicati, ed entrambi sono fonte singola per la propria
  direzione. **Corretto così com'è.**

### C.6 Qualità dei commenti — un difetto concreto

`functionsFrontend.inc:842-858` contiene **lo stesso blocco di commento di 8 righe, due volte,
verbatim**:

```
842: // CAMBIA STAMPA (F4.5 #55, stato finale stagisti HEAD 0632388): una card
843: // per stampante con pallino verde/rosso (stampante_switch_ok sopra), grid 2x2
844: // ($rpp=4), raggiungibili prime, senza riga STATO (272aee3). Solo la GX420t
845: // e' dual: scelta ZPL/EPL via pill, SELEZIONA disabilitato finche' non scegli;
846: // le altre mostrano badge fisso ZPL/EPL + SELEZIONA diretto. Selezione
847: // irraggiungibile bloccata (tranne RETE ping-only).
848: // Pattern mostraPrintReset: isAdmin + POST + CSRF + confirm + impostaStampanteSelezionata().
849: // CAMBIA STAMPA (F4.5 #55, stato finale stagisti HEAD 0632388): una card
850: // per stampante con pallino verde/rosso ... ← identico, ripetuto
...
858: // F2.5 #92: unica implementazione vera in ...
```

**Informational.** Un refuso da copia-incolla nel file che serve da riferimento di navigazione
principale del progetto. La metà duplicata sparisce gratis quando `stampante_switch_ok` e
`mostraSwitchPrinter` vengono cancellate.

### C.7 Esempi concreti di buon design (da preservare)

Tre cose in questo file sono genuinamente migliori della media e devono sopravvivere a
qualsiasi cambiamento futuro:

1. **`coloreCategoriaHex` (87-111)** — una lookup chiusa con una traccia decisionale
   documentata: quali colori sono stati scelti, da chi, perché il nero è escluso, e i
   rapporti di contrasto misurati per ciascuno contro `#2b3d4e` (righe 85-86). È esattamente
   il commento che dovrebbe esistere quando una decisione di business è codificata.

2. **`cassa_azione_login` (120-157)** — l'handler di auth è *insolitamente* accurato per un
   kiosk: fa `unset` della sessione admin precedente **prima** di verificare (riga 127, così un
   tentativo fallito non può coesistere con un pannello), combina un blocco session-scoped con
   il throttle persistente su file (riga 130, così sopravvive alla cancellazione del cookie),
   usa `password_verify` contro un hash env senza percorso in chiaro da nessuna parte, chiama
   `session_regenerate_id(true)` al successo (riga 138), e non lascia mai che il codice finisca
   in URL o log (riga 123). Il path di fallimento incrementa un contatore, blocca 60s a 5, e
   redirige senza dettagli d'errore.

3. **`tagliaVisibile` (468-477)** — codifica una regola fisica sottile: *il taglio scatta solo
   su carta continua con GX420t, mai sui biglietti singoli.* Il commento dichiara l'invariante
   ("in biglietti singoli il pulsante non appare mai e il taglio non scatta mai") e il codice
   compone tre gate indipendenti. Uno sviluppatore che cambia la config della stampante qui
   romperebbe un comportamento fisico che non troverebbe leggendo il codice.

### C.8 Valutazione coesione / accoppiamento / leggibilità / testabilità

| Dimensione | Valutazione | Evidenza |
|---|---|---|
| **Coesione** | **Buona** dopo la rimozione del codice morto | Il codice vivo ha 3 cluster dichiarati, ciascuno internamente coerente. L'header del file (righe 3-11) dichiara il suo ruolo e il suo stato di migrazione in modo onesto. |
| **Accoppiamento** | **Basso in uscita, moderato in entrata** | Dipende da `OrderService`, `CatalogRepo`, `CatalogView`, `AdminView`, `CassaConfig`, `CassaFlags`, `PrinterRegistry`, `PrinterConfig`, `Storage`, `Env`, `Throttle. Internamente i 11 handler condividono l'idioma `$post_ok` (coerente = buono) e `tagliaVisibile()` è condiviso da 2 caller (va bene). |
| **Leggibilità** | **Buona** | `declare(strict_types=1)`, no globali, no magia, ogni funzione documentata. L'unico difetto di leggibilità è il blocco commenti duplicato (§C.6). |
| **Side effect al caricamento** | **Nessuno** | Verificato: il file contiene solo dichiarazioni di funzione. È stata la correzione F3.2 #95 ed ha tenuto. |
| **Testabilità** | **Scar��, e strutturalmente così** | `CassaViewTest.php:70-86` deve fare `eval()` di 24 stub di funzione; `CassaViewTest.php:90-96` deve estrarre `coloreCategoriaHex` dal sorgente con una regex per testarla. `PowerF2Test.php` ha bisogno di `#[RunTestsInSeparateProcesses]` per collisioni tra stub. **Causa radice: gli emitter di vista sono funzioni libere nel namespace globale, non una classe.** |
| **Località del cambiamento** | **Buona per i cluster vivi** | "Cambia il pulsante quantità" → righe 254-264. "Cambia il gate stampante" → riga 468. Niente richiede di leggere sezioni non correlate. |

La riga sulla testabilità è la più importante negativa. **Non è causata dalla lunghezza** — è
causata dalla *forma* del layer di presentazione. Questo è l'argomento reale per un cambio in
questo file, ed è un argomento molto più debole di "875 righe è troppo".

### C.9 VERDETTO FINALE su `functionsFrontend.inc`

**La lunghezza di ~800 righe è di per sé un problema significativo? — No.**

Il file ha 61 funzioni con media di 14 righe, zero classi, zero globali a top-level, zero
nidificazione profonda, zero logica duplicata, zero side effect all'include, e un header che
descrive accuratamente il suo ruolo. Su ogni misura strutturale disponibile, 875 righe sono
poco notevoli. **Il conteggio righe non è il difetto e non baserò alcuna raccomandazione su
di esso.**

**Ha duplicazione o complessità inutile dimostrabili? — Minime.** Due elementi reali: la
normalizzazione barcode tripla (§C.5, ~4 righe) e un blocco commenti duplicato (§C.6, 8 righe).

**La sua organizzazione interna è sufficiente? — Sì, per il codice vivo.** Tre cluster,
firma handler uniforme, idioma `$post_ok` consistente, forma di delega sottile coerente. Non
ho trovato un esempio concreto di organizzazione confusa nella parte viva.

**Le funzioni sono coese? — Sì.** 11 adapter HTTP che fanno HTTP, 14 emitter che emettono,
8 helper che aiutano. Nessuna funzione mescola responsabilità non correlate.

**Dovrebbe essere spezzato? — No.** Ecco perché, valutato contro le alternative:

- **Opzione 1 (tenere com'è):** rifiutata. Porta 171 righe di codice irraggiungibile e 9
  contratti di compatibilità obsoleti che attivamente fuorviano il maintainer. Non è "tenere",
  è "lasciare la migrazione a metà".
- **Opzione 2 (riorganizzazione interna, senza split):** rifiutata come insufficiente di per sé
  — il problema non è l'organizzazione, è la presenza.
- **Opzione 3 (estrazione di un numero ridotto di responsabilità coese):** **parzialmente
  corretta, ma per il motivo sbagliato.** Due estrazioni sono giustificate, ma non per il
  conteggio righe:
  - **(a) Cancellare le 17 funzioni morte.** Non un'estrazione: una cancellazione. Toglie 171
    righe (19,5%) e 9 contratti falsi. **Questa è la modifica.**
  - **(b) Completare lo split SQL/vista per i 5 emitter che portano SQL**, dando a ciascuno un
    accessor dati (lato `CatalogView`/`OrderService`) e lasciando l'emitter come echo puro.
    Stesso pattern che il progetto ha già applicato con successo a `mostraNavCategorie`,
    `mostraTitolo`, `mostraTabellaProdotti`, `mostraBarraBarcode`. Questo completa la convenzione
    della migrazione stessa invece di inventarne una nuova. Sposterebbe via circa 5 statement SQL
    e renderebbe le funzioni vista testabili senza stub `eval()`.
- **Opzione 4 (riorganizzazione più ampia):** rifiutata. Non c'è god object, nessuna dipendenza
  circolare, nessun problema "a forma di framework". `src/` è già ben fattorizzato. Un
  riscrittura distruggerebbe struttura funzionante senza guadagno misurabile.

**Cosa rende la struttura corrente accettabile:** l'organizzazione in tre cluster, la firma
uniforme degli handler, l'intento documentato nell'header del file. **Cosa la rende
attualmente inaccettabile:** 19,5% di codice irraggiungibile più contratti obsoleti che
dichiarano necessarie funzioni che non lo sono.

**Quali miglioramenti interni valgono più di uno split?** In ordine di priorità:
(1) cancellare il codice morto; (2) completare lo split SQL/vista per i 5 superstiti;
(3) spostare completamente le responsabilità dispatch dell'era `gestisciAzioni` in
`CassaController` e cancellare del tutto l'indirection handler; (4) deduplicare la
normalizzazione barcode; (5) correggere il commento duplicato (gratis con il punto 1).

**Obiettivo dopo la rimedio: ~450-500 righe vive.** Non propongo un target di righe — quel
numero discende dalle cancellazioni. Se risultasse 500 o 600, la struttura è ugualmente
corretta.

---

## D. Class and Function Review

### D.1 Inventario classi

23 classi + 2 enum + 5 gruppi di funzioni globali. Esaminate tutte.

**Ben progettate, tenere così:**

| Classe | Righe | Perché è giusta |
|---|---|---|
| `OrderService` | 298 | `$db` iniettato via costruttore. Ogni operazione è scoped in transazione. `SELECT ... FOR UPDATE` con lock del padre per primo e ordine di lock costante anti-deadlock documentato (righe 196-198). `calcolaTotali($in_txn)` evita correttamente un `begin_transaction` annidato (le transazioni MySQL fanno implicit commit su begin annidato — è un dettaglio di correttezza sottile che hanno gestito bene e documentato). Test da 237 righe con DB finto. **Miglior classe del repository.** |
| `CassaController` | 54 | Fa esattamente tre cose: risolve l'azione, autorizza, dispatch. Esplicitamente *non* tocca soldi né HTML. Test da 111 righe. |
| `CassaView` | 233 | Dispatch `match` puro + metodi privati `corpo*`. Nessuna lettura di `$_GET`/`$_POST` (deliberato, documentato alle righe 20-28). Test da 179 righe. |
| `CatalogRepo` / `CatalogView` | 106/109 | Split perfetto: dati / render, zero sovrapposizione. |
| `Fiscale` | 299 | Ogni metodo ritorna un array di esito, non lancia mai. Sender iniettabile per i test. Idempotenza via `giaEmesso()`. Clamp dei timeout (1-10s connect, 1-30s total) con razionale. Test di integrazione da 67 righe. |
| `Session` | 46 | `use_strict_mode=1`, `HttpOnly`, `SameSite=Lax`, `Secure` solo sotto HTTPS (corretto: la fiera gira su HTTP in chiaro — documentato a riga 10). |
| `Env` | 47 | Loader `.env` di 12 righe, env di sistema vince. `log()` whitelista i livelli e non logga mai payload — imposto per convenzione e commentato a riga 14 di `ErrorHandler.php`. |
| `Throttle` | 77 | Su file, così sopravvive al reset della sessione. Testato esattamente per questo (`SessionThrottleTest.php:41-50`). |
| `BackupRun` | 177 | `MYSQL_PWD` in env invece che sulla command line, esplicitamente per evitare esposizione in process list (commento alle righe 80-81). `escapeshellarg` su ogni pezzo. Nomi tabella whitelisted. |

**Confini di responsabilità discutibili:**

| Elemento | Problema | Severity |
|---|---|---|
| `AdminView` (494 righe) | Contiene 13 renderer che coprono feature admin *non correlate*: pannello, tastierino, contatori, info festa, repair DB, modalità carta, switch stampante (inclusi ~120 righe di JS inline e logica grid/paginazione), diagnostica power, restart, shutdown. Ogni metodo è coerente individualmente; la *classe* è un sacco misto. `switchPrinter()` da solo è righe 312-431 (120 righe, ~24% della classe) e contiene sia presentazione sia orchestrazione della sonda di raggiungibilità. **Però**: ogni metodo è admin-gated, nessuno ha logica di dominio, e dividere una classe renderer statica per feature creerebbe 4 file senza comportamento condiviso. **Low.** Se `functionsFrontend.inc` verrà toccata ancora, questo è il candidato successivo — non prima. |
| `VisualizzaStore` (210 righe) | Il nome dice "Store" (persistenza) ma contiene 7 responsabilità su 8 non legate a memorizzare: normalizzazione colori, etichette SI/NO, traduzione messaggi d'errore, costruzione URL. L'unico metodo davvero store è `fetchPagina`. **Informational** — un rename sarebbe cosmetico. |
| `FestaConfig` (29 righe) | Delega pura a `CassaFlags::festaLeggi/festaImposta`. Zero caller trovati nel codebase. Questa è la "interfaccia con una sola implementazione" che il progetto ha esplicitamente detto di non creare — e l'ha creata comunque. **Informational**, candidato alla cancellazione. |
| `OrderType` / `PayMethod` | Enum, correttamente sostitutivi di whitelist copiate a mano. `OrderType::lettera()` che ritorna `?string` è un'API leggermente strana per una preoccupazione di display, ma la distinzione null-vs-'NORMALE' è reale. **Tenere.** |
| `LabelBuilder` (592 righe) | Cinque funzioni builder pure in un file namespaced, non una classe. `$str[0] = $str1;` (riga 91) usa un array a variabile non definita — funziona, ma è legacy. `generaCardDegustazione()` (riga 230) è chiamata dall'interno di `etichetta_continua()` **ed esegue il proprio `fopen`/`fwrite`** su disco (righe 291-293) — un builder *puro* che scrive file, chiamato come side effect della costruzione di un'altra label. È l'unico punto in `src/` dove presentazione e I/O sono genuinamente intrecciati. **Medium** — vedi REL-04. |
| `PrintService` (281) | `generaFileStampa()` mescola lettura config, valutazione gate, generazione label, scrittura spool e dispatch di trasmissione (righe 22-168). Il ramo di fallimento gate (45-65) fa `print` di un blocco HTML dall'interno di un servizio — un servizio che ritorna `false` su fallimento e stampa UI su un *fallimento diverso*. Contratto incoerente. **Low.** |

### D.2 Helper condivisi — verifica di coerenza

`src/Support/helpers.php` è 62 righe, 6 funzioni, zero side effect al caricamento, ognuna
guardata da `if (!function_exists(...))`. Convenzioni di ritorno: `csrf_*` ritorna
`string`/`bool`/`void`, `mysql_query_safe` ritorna `mysqli_result|false`, `db_select`
`mysqli_result|false`, `db_exec` `bool`. **Coerenti.** Nessun rilievo.

L'unico problema di naming, già segnalato dalla documentazione di architettura del progetto
(`ARCHITETTURA_REVISTA.md:384-386`): **`mysql_query_safe()` mente.** Chiama `Db::query()` →
`mysqli_query()` grezzo senza alcun escaping. Il nome promette sicurezza che non fornisce.
Oggi è innocuo (9 call site, tutti SQL statico, verificati), ma il nome è una trappola per il
prossimo sviluppatore. **Low severity, alto valore per riga modificata** (rename in
`db_query` o `db_query_raw`).

### D.3 Backoffice (`src/Backoffice/`)

Il pattern a 5 tab (`*Tab::config()` / `::elimina()` / `::caricaModifica()` / `::salva()`) è
genuinamente ben fattorizzato. Ogni mutazione richiede POST + `csrf_ok()`; ogni `elimina()`
rifiuta se referenziato e rifiuta il GET con `AZIONE NON VALIDA` (`CategorieTab.php:44-46`).
ORDER BY è solo da whitelist (`VisualizzaStore::resolveOrderBy`), LIKE è bound
(`buildSearch`), LIMIT è cast `(int)`. Il router avvolge tutto in `try/catch(Throwable)` per
schema drift (`visualizza.php:281-300`). **Nessun rilievo.** È meglio ingegnerizzato della
media dei backoffice PHP in produzione.

---

## E. Findings Register

### REL-01 — Emissione di scontrino fiscale non autenticata via parametro GET non scoped

- **Categoria:** Correttezza / Sicurezza
- **Severity:** **High**
- **Confidence:** **High** (code path tracciato interamente, nessuna ambiguità)
- **Location:** `src/Cassa/StampaController.php:95-119`

**Evidenza:**

```php
95:  $id_ordine = (int)$_GET['stampato'];
96:  // Totale sempre dal DB via stampato, mai dal GET (display resto non fidato).
98:  $risTot = db_select($db, "SELECT totale FROM ordini WHERE id_ordine = ?", 'i', array($id_ordine));
...
110: if ($metodo !== '') {
111:     try {
112:         $fisc = \Salsiccia\Fiscale\Fiscale::emettiScontrino($db, $id_ordine, $metodo);
```

`id_ordine` viene da `$_GET['stampato']` ed è usato in **due** query, **nessuna** delle quali
vincola `id_cassa`:

- Riga 98: `SELECT totale FROM ordini WHERE id_ordine = ?` — nessun `id_cassa`, nessun
  predicato `chiuso`.
- `Fiscale::emettiScontrino` (`src/Fiscale/Fiscale.php:265`):
  `SELECT SUM(...) FROM righe_ordini, prodotti WHERE ... AND righe_ordini.id_ordine = ?` —
  nessun `id_cassa`.

Il ramo di errore a riga 32 **scopia correttamente** (`WHERE id_ordine = ? AND id_cassa = ?
AND chiuso = '0'`), prova che lo scoping era noto e applicato in un punto ma non nell'altro.

**Flusso dati:** HTTP GET → `public/index.php?action=s&stampato=<qualsiasi int>&metodo=contanti`
→ `CassaController::gestisci` (`routes/cassa.php:30` — `'s' => handler: null, auth: null`) →
`CassaView::corpoStampa` (`CassaView.php:118`) → `StampaController::stampa($db)`. Nessuna
auth, nessun CSRF, nessun requisito di sessione.

**Conseguenze:**

1. **Integrità dei record finanziari.** Chiunque raggiunga la porta HTTP del kiosk (la LAN di
   fiera, un port-forward sbagliato, un prefetch del browser) può far emettere uno scontrino
   fiscale al dispositivo fiscale registrato per un ordine arbitrario, attribuendo un metodo di
   pagamento che non è mai stato versato. `Fiscale::giaEmesso()` (`Fiscale.php:227-245`) impedisce
   una *seconda* emissione dello stesso `id_ordine`, quindi la **prima** emissione è sempre
   disponibile per l'abuso.
2. **Esposizione dati cross-terminal.** Su un DB condiviso con più `id_cassa` (il pannello admin
   supporta 1-999, `functionsFrontend.inc:25,182`), il terminale A può leggere i totali del
   terminale B iterando su `id_ordine`.
3. Il valore `stampato` viene anche concatenato in `$base_url` (riga 106) e da lì in ogni
   pulsante banconota/moneta nel `onclick` (`StampaView.php:43`), quindi l'`id_ordine` avvelenato
   persiste su tutta la schermata di pagamento.

**Riferimento:** OWASP CSRF Prevention Cheat Sheet — *"All state changing operations should only
be performed using HTTP POST"* e *"State changing operations should also be protected against
CSRF"*. Nota: l'esposizione pratica immediata è limitata perché la schermata kiosk è
intenzionalmente aperta (nessun login sulla schermata principale), quindi non è primariamente
un problema di classe CSRF — è un problema di **scope di autorizzazione mancante**.
Tokenizzare da solo non lo risolverebbe; lo scoping delle query deve cambiare.

**Direzione raccomandata:** scopare entrambe le query per `id_cassa` e `chiuso`, in modo
coerente con la riga 32. Spostare il ramo dietro guard POST + CSRF, oppure verificare lato
server che l'ordine sia stato effettivamente stampato da *questo* terminale (la colonna
`num_biglietti`, impostata a `StampaController.php:89`, esiste esattamente per registrarlo ed è
attualmente inutilizzata come guardia).

**Priorità:** 1 — la più alta.
**Effort:** Small.

---

### REL-02 — 17 funzioni irraggiungibili / 171 righe morte nel file target primario

- **Categoria:** Manutenibilità
- **Severity:** **Medium**
- **Confidence:** **High** (scansione esaustiva del call graph, commenti rimossi, ref a stringa contati)
- **Location:** `functionsFrontend.inc` — 17 funzioni, enumerate in §C.2

**Evidenza:** tabella completa in §C.2. Ognuna ha zero caller su tutti i 76 file non-vendor.
`CassaView` chiama `AdminView::*` direttamente per tutte le 9 viste admin. I doc-comment
asseriscono che gli shim sono contratti richiesti.

**Conseguenze:**

- Il 19,5% del valore di navigazione del file è fiction.
- Un maintainer che fa il prossimo refactor deve preservare 9 contratti inesistenti.
- `CassaViewTest.php:70-86` fa `eval()` di stub su 6 di questi nomi, quindi la suite non può
  rilevarne l'assenza.
- Maschera il vero stato della migrazione in code review — un reviewer deve ri-derivare il
  call graph per scoprire che sono morte.

**Riferimento di supporto:** il criterio dichiarato dal progetto stesso,
`docs/ARCHITETTURA_REVISTA.md:403-407`: *"Un file = un motivo per cambiare"* e *"le dimensioni
seguono; non le guidano"*. Secondo quel criterio il codice vivo passa; il codice morto fallisce
perché non è un motivo per cambiare nulla — non è niente.

**Direzione raccomandata:** cancellare tutti i 17 corpi di funzione e i loro doc-comment.
Aggiornare l'header del file (riga 3, nota `T31` "rivalutare entro 2026-12-31") e rimuovere le
voci corrispondenti dalla lista di follow-up `T31`. Rimuovere gli stub `eval()` ora superflui da
`CassaViewTest.php`.

**Svantaggi:** nessuno identificato. Cancellazione pura. L'unico rischio è che esista un call
path non cercato — ho controllato tutti i `.php`, `.inc` del repo e i riferimenti a stringa di
`routes/cassa.php`, e la tabella di routing non nomina nessuna di queste.

**Priorità:** 2.
**Effort:** Small.

---

### REL-03 — Denaro rappresentato come `float` IEEE-754 end-to-end

- **Categoria:** Correttezza
- **Severity:** **Medium**
- **Confidence:** **High**
- **Location:** `database/migrations/0001_schema.sql:52` (`prodotti.prezzo float`), `:77`
(`righe_ordini.totale float`), `:42` (`ordini.totale float`), `:53` (`prodotti.iva float`);
aritmetica a `src/Cassa/OrderService.php:219-220`, `:233`, `:240`; display a
`functionsFrontend.inc:422,535,579,615`; fiscale a `src/Fiscale/Fiscale.php:59-60`

**Evidenza:**

```php
// OrderService.php:219-220
$prezzo = (float)$riga['prezzo'];
$totale = $quantita * $prezzo; // moltiplicazione in binary float
...
// OrderService.php:232-233
$totale = (float)$riga['totale'];       // SUM() su colonne float
// Fiscale.php:59-60
$k = number_format((float)($riga['iva'] ?? 0), 2, '.', '');
$cent = (int)round((float)($riga['totale'] ?? 0) * 100);   // ← la riga critica per il denaro
```

**Conseguenze:**

- **Arrotondamento rilevante per le imposte.** `Fiscale.php:60` è l'unico punto in cui il
  denaro diventa intero, e passa da binary float. Un totale come `8.20` può essere
  `8.199999999999999`, facendo produrre a `round(...*100)` un `820` per fortuna anziché per
  garanzia. La modalità di guasto è un **importo in centesimi sbagliato su un documento
  fiscale**, intermittente e sostanzialmente non diagnosticabile a posteriori.
- **Deriva accumulata.** `ordini.totale` è una `SUM()` su totali di riga float. Lo schema
  stesso lo anticipa: `0003_innodb.sql` mantiene `float` convertendo a InnoDB.
- Il display è coerentemente `number_format(..., 2, ',', '.')`, quindi i totali *mostrati* sono
  sempre ben formati — il che significa che una discrepanza di arrotondamento tra ciò che il
  cliente vede e ciò che viene registrato è possibile e **non sarebbe visibile al personale**.

**Riferimento:** manuale PHP sulla precisione dei float —
<https://www.php.net/manual/en/language.types.float.php> documenta che `float` ha precisione
limitata e raccomanda la rappresentazione basata su interi (o BCMath) per valori dove la
precisione conta. Nessun framework o standard annulla questo; è inerente al tipo.

**Direzione raccomandata:** migrare `prezzo`, `totale`, `iva` a `DECIMAL(10,2)` /
`DECIMAL(10,4)` e calcolare in centesimi interi, oppure adottare BCMath per l'aritmetica in
`OrderService::calcolaTotali` e `Fiscale::buildXml`. È una **migrazione di schema più
cambiamento aritmetico** e deve essere validata su dati reali di fiera prima di andare in
produzione.

**Svantaggi / proporzionalità:** questo è il rilievo in cui "fare meno" merita una nota onesta.
I prezzi di questa fiera sono quasi certamente valori interi in euro o a due decimali che
capitano per rappresentarsi in modo pulito, e il sistema gira da più fiere. La modifica è
lavoro reale con rischio di regressione sul percorso più critico. **Va pianificata
deliberatamente con confronto dati, non infilata in una pulizia.** Non lo raccomando come
urgente; lo raccomando come *il* rilievo che morderà prima o poi e che dovrebbe essere
posseduto anziché scoperto.

**Priorità:** 3 — ma da schedulare presto e validare duramente.
**Effort:** Medium (schema + codice + verifica dati).

---

### REL-04 — Builder puro di etichette che esegue I/O su file come side effect

- **Categoria:** Correttezza / Architettura
- **Severity:** **Medium**
- **Confidence:** **High**
- **Location:** `src/Cassa/LabelBuilder.php:230-294` (`generaCardDegustazione`), chiamata da
`etichetta_continua()` alle righe 420-446

**Evidenza:** `generaCardDegustazione()` è documentata come builder puro (header del file riga 8:
*"Restano funzioni pure (calcolano e ritornano, §7 vieta classi-wrapper)"*), eppure alle righe
291-293 fa:

```php
$pf = fopen(dirname(__DIR__, 2) . "/storage/labelCards", "w") or die("Impossibile aprire il file di stampa");
fwrite($pf, $label, strlen($label));
```

e il caller esegue subito `system()` con una `lpr` (righe 438-439) per spingerla in stampante.

**Conseguenze:**

- Chiamare `etichetta_continua()` per i prodotti 14/15/16 innesca una **seconda stampa
  fisica** come side effect invisibile. Se `lpr` si blocca o si impianta (riga 439 non ha
  timeout), l'intera richiesta `?action=s` si blocca.
- `die()` su fallimento di `fopen` (riga 292) significa che una directory `storage/` mancante
  uccide l'intera richiesta di stampa con una stringa grezza, bypassando `ErrorHandler`.
- Il path **non** passa da `Support\Storage::path()`, quindi salta la guardia anti-traversal
  `basename()` che ogni altro path storage usa (`Storage.php:29`). Attualmente è un letterale
  costante quindi non è sfruttabile, ma è l'unica scrittura in storage in `src/` che salta la
  guardia.
- **La correttezza dello split è per il resto buona:** `etichetta_continua()` ritorna
  `array(label, num_pezzi)` ed è coperta da `tests/Unit/LabelBuilderTest.php`, ma quel test
  esercita per forza anche la scrittura su file.

**Direzione raccomandata:** far ritornare a `generaCardDegustazione()` la sua stringa label come
fanno le sorelle; spostare la sequenza `fopen`/`fwrite`/`lpr` in `PrintService`, che già possiede
spool-and-send (`PrintService::inviaFileStampa()`). Usare `Storage::path('labelCards')` per la
guardia. Aggiungere un timeout alla chiamata `lpr`.

**Priorità:** 4.
**Effort:** Small-Medium.

---

### SEC-01 — `mysql_query_safe()` promette una sanitizzazione che non esegue

- **Categoria:** Sicurezza (latente)
- **Severity:** **Low**
- **Confidence:** **High**
- **Location:** `src/Support/helpers.php:43-48` → `src/Support/Db.php:39-51`

**Evidenza:** il docblock a `Db.php:34-38` è onesto (*"Esegue SQL grezzo senza alcun
escaping"*; *"NON sanifica, interpola ciò che riceve"*), ma la funzione si chiama
`mysql_query_safe`. Ho verificato tutti i 9 call site — `CatalogRepo.php:29,82`,
`StatsData.php:113`, `DbRepair.php:21,31`, `VisualizzaStore.php:83,204`,
`BackupRun.php:115,137,144` — e **ognuno passa una stringa letterale statica**. Esposizione
attuale zero.

**Conseguenze:** un futuro sviluppatore che raggiunge `mysql_query_safe()` per via del nome
costruirà un'injection. La documentazione di architettura del progetto segnala già questo
rischio esatto: *"il nome è un avviso per il prossimo"* (`ARCHITETTURA_REVISTA.md:384-386`).

**Direzione raccomandata:** rinominare in `db_query_raw()` o `db_static_query()`. Rename puro,
9 call site, verificato dal compilatore.

**Priorità:** 5.
**Effort:** Small.

---

### MAINT-01 — Normalizzazione input barcode implementata tre volte

- **Categoria:** Manutenibilità
- **Severity:** **Low**
- **Confidence:** **High**
- **Location:** `functionsFrontend.inc:241-242` (`cassa_azione_barcode`), `:386-387`
(`mostraBarraBarcode`), `src/Catalog/CatalogRepo.php:96-99`

**Evidenza:** il `substr(trim($raw), 0, 20)` + check sentinella `'-'` compare in tutti e tre i
siti; la versione nel repo è quella canonica e ritorna già `null` per empty/`'-'`.

**Conseguenze:** il cap a 20 è la larghezza della colonna DB (`0001_schema.sql:57`,
`varchar(20)`). Se cambia, tre siti devono cambiare insieme e solo
`tests/Unit/ProdottiBarcodeTest.php` ne copre uno.

**Direzione raccomandata:** rimuovere i check duplicati dai due caller; affidarsi al repo.
Entrambi i caller già ramificano su `null`.

**Priorità:** 6.
**Effort:** Small.

---

### MAINT-02 — Cinque funzioni vista continuano a interrogare il database inline

- **Categoria:** Architettura / Testabilità
- **Severity:** **Low**
- **Confidence:** **High**
- **Location:** `functionsFrontend.inc:347` (`mostraIndicatoreTipo`), `:400`
(`mostraModificaOrdine`), `:514` (`mostraOrdiniStandby`), `:570` (`mostraBoxRiepilogo`),
`:605` (`mostraListaProdotti`)

**Evidenza:** queste 5 contengono ancora `db_select`/`mysql_query_safe` più i loro corpi echo.
Le loro 4 sorelle già migrate (`mostraNavCategorie`, `mostraTitolo`, `mostraTabellaProdotti`,
`mostraBarraBarcode`) sono deleghe pure di 3-8 righe a `CatalogView`/`CatalogRepo`.

**Conseguenze:** è questo il motivo per cui `CassaViewTest.php` ha bisogno di 24 stub `eval()`
(righe 70-86) e di un'estrazione regex di `coloreCategoriaHex` dal sorgente (righe 90-96). Il
costo di testabilità del file è attribuibile a queste 5 funzioni, non alla sua lunghezza. Significa
inoltre che il progetto ha due convenzioni per lo stesso lavoro dentro un file solo — un difetto
di coerenza reale (viola il pattern che il progetso stesso ha stabilito).

**Direzione raccomandata:** completare il pattern esistente. Aggiungere i metodi di lettura dove
serve (`OrderService` per le letture ordine/carrello, `StatsData` per lo standby) e lasciare gli
emitter come echo puro. Non creare una nuova classe.

**Priorità:** 4 (insieme a REL-04).
**Effort:** Medium.

---

### INFO-01 — Residuo strutturale: dispatch morto, classi ridondanti, commento duplicato

- **Categoria:** Coerenza
- **Severity:** **Informational**
- **Confidence:** **High**
- **Location:** `functionsFrontend.inc:849-858` (commento duplicato verbatim);
`src/Config/FestaConfig.php` (zero caller); `src/Backoffice/VisualizzaStore.php` (8 metodi su 9
non sono storage)

**Evidenza:** tutti e tre verificati per ricerca diretta.

**Conseguenze:** basse. La duplicazione del commento sparisce gratis con REL-02. `FestaConfig` è
una delega pura di 29 righe senza caller — il progetto ha esplicitamente detto di non creare
wrapper così (`ARCHITETTURA_REVISTA.md:506-520`), quindi è una piccola auto-incoerenza.
`VisualizzaStore` è solo un imprecisione di nome.

**Direzione raccomandata:** cancellare `FestaConfig`; valutare un rename di `VisualizzaStore` →
`VisualizzaShared` o lo split degli helper colore. Entrambi opzionali.

**Priorità:** 7.
**Effort:** Small.

---

### Rilievi verificati esplicitamente e **non** sollevati

Registrati qui perché un reviewer futuro non li riveda da zero:

- **SQL injection:** nessuno. Tutte le query che portano input utente usano prepared statement
  con parametri bound. Verificato in `OrderService` (tutte e 15), `CatalogRepo` (6),
  `VisualizzaStore` (ORDER BY da whitelist, LIKE bound, LIMIT `(int)`), `StatsData` (4, tutte
  prepared), `Fiscale` (1, prepared), tutti e 5 `*Tab.php`, `login.php`, `BackupRun` (nomi
  tabella whitelisted).
- **XSS:** nessun percorso iniettabile trovato. Ogni valore di `$_GET`/`$_POST`/`$_SERVER`
  echoed in HTML passa per `htmlspecialchars(..., ENT_QUOTES)`. Ho verificato specificamente
  gli hit del grep di echo non escapati in `visualizza_view.php` e `statistiche.php` — ognuno è
  un cast `(int)`, un letterale di tastiera hardcoded, o una lookup su array di config.
- **Session fixation / cookie hardening:** corretto. `session_regenerate_id(true)` su entrambi i
  path di auth, `use_strict_mode=1`, `HttpOnly`, `SameSite=Lax`.
- **Command injection:** nessuno. Tutti i 12 call site di `exec`/`system`/`proc_open`/`shell_exec`
  usano `escapeshellarg` per argomento, e tutti gli input variabili sono inoltre validati con
  whitelist (`/^[A-Za-z0-9_-]+$/`). `BackupRun` usa `MYSQL_PWD` in env invece della password sulla
  command line specificamente per evitare esposizione via `/proc`.
- **Esposizione di segreti:** `.env` è gitignored e non tracciato (verificato via `git ls-files`).
  Nessuna credenziale nel sorgente. `storage/*.log` contiene importi fiscali ma `storage/` è
  fuori docroot con il proprio `.htaccess`.
- **Migrazione a framework:** non giustificata. Il progetto è ben organizzato, completamente
  testato dove conta, e l'albero `src/` segue già una convenzione coerente.
- **Estensione `.inc`:** la documentazione di architettura la chiama reliquia (`§7`). I due file
  rimanenti sono `functionsFrontend.inc` (solo funzioni, no side effect — sicuro) e
  `reserved/auth_password.inc` (solo funzioni). Entrambi fuori docroot. **Nessuna esposizione
  viva.** L'estensione è cosmetica.

---

## F. PHP Best Practices and External Research

**Vincolo di versione applicato ovunque:** `composer.json:7` richiede `^8.2`; il runtime locale è
PHP 8.2.12. Ogni raccomandazione qui sotto funziona su 8.2. Niente richiede 8.3+.

### F.1 Cosa il progetto già fa correttamente

**Session security** — <https://www.php.net/manual/en/session.security.php> e
<https://www.php.net/manual/en/function.session-set-cookie-params.php>.
`Session::start()` (`src/Support/Session.php:32-45`) imposta `HttpOnly`, `SameSite=Lax`,
`use_only_cookies=1`, `use_strict_mode=1`, e `Secure` condizionalmente.
`session_regenerate_id(true)` (sessione vecchia cancellata) è chiamato su entrambi i login
riusciti (`functionsFrontend.inc:138`, `login.php:44`). Questo supera le raccomandazioni del
manuale PHP.

**CSRF** — <https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html>.
Il "Synchronizer Token Pattern" di OWASP è esattamente ciò che `helpers.php:12-37` implementa:
token random `bin2hex(random_bytes(32))` in sessione, confrontato con `hash_equals()`, emesso via
`csrf_field()` in ogni form mutante. Tutti gli 11 handler `cassa_azione_*` e tutti e 5
`*Tab::elimina()`/`::salva()` lo verificano. **Corretto secondo la raccomandazione primaria di
OWASP.**

**SQL injection** — <https://www.php.net/manual/en/security.php#security.database.sql-injection>.
Tutti i dati variabili sono bound. Il pattern whitelist-ORDER-BY in
`VisualizzaStore::resolveOrderBy()` e `StatsData::ordinaPerProdotto()` è la difesa corretta per
l'unico costrutto che i placeholder non possono coprire.

**Password** — <https://www.php.net/manual/en/faq.passwords.php>. Argon2id via
`password_hash(..., PASSWORD_ARGON2ID)` (`reserved/auth_password.inc:17`), `password_verify`
(`:41`), rehash trasparente con `password_needs_rehash` (`:50`). L'emulatore del legacy MySQL
`PASSWORD()` (`:27-31`) è isolato, documentato come solo-migrazione, e usa correttamente
`hash_equals`. `CassaConfig::carica()` (`src/Config/CassaConfig.php:29-33`) si rifiuta di
avviare se l'hash admin manca o non è un hash valido — fail-closed, corretto.

**Error reporting** — <https://www.php.net/manual/en/security.php#security.error-handling>.
`ErrorHandler::registra()` imposta `display_errors=0`, `error_reporting(E_ALL)`, e installa
handler di eccezioni e di shutdown. Logga classe + messaggio + file:linea + action, esplicitamente
**mai payload** (`ErrorHandler.php:13-14`) — quindi nessun codice admin né importo arriva nel log.
Buona igiene operativa per un dispositivo che manca contanti.

**PSR-12** — <https://www.php-fig.org/psr/psr-12/>. `declare(strict_types=1)` su ogni file in
`src/` (§3). PSR-12 richiede il declare nella riga successiva a `<?php` — soddisfatto ovunque.
PHPCS riporta **0 errori**. I 265 warning sono note di lunghezza riga e side-effect, che
`phpcs.xml:8` configura esplicitamente come non bloccanti (`ignore_warnings_on_exit=1`), con il
razionale documentato a `phpcs.xml:4`. È una decisione ponderata, non una dimenticanza.

**PSR-4 autoloading** — <https://www.php-fig.org/psr/psr-4/>. `composer.json:14-21` mappa
`Salsiccia\` → `src/`. I nomi file corrispondono esattamente ai nomi classe. La
`ARCHITETTURA_REVISTA.md:489-499` del progetto nota correttamente che PSR-4 impone `.php` e che
`autoload.files` è il meccanismo ufficiale di Composer per le funzioni globali — e `helpers.php`
lo usa. **Il ragionamento è solido e correttamente applicato.**

### F.2 Dove l'implementazione si discosta dalla guida, e se conta

**Il report mode di default di `mysqli` ora solleva eccezioni (PHP 8.1+)** —
<https://www.php.net/manual/en/mysqli-driver.report-mode.php>: *"As of PHP 8.1.0, the default
setting is MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT."* Confermato su questo runtime
(`ini_get('mysqli.report_mode')` = `false`, cioè il default). Il codebase è **consapevole e
coerente** con questo: `OrderService` ha blocchi `catch (\Throwable)` con il commento *"PHP8:
query fallita lancia invece di tornare false"* alle righe 91, 136, 168, 245. `visualizza.php:281`
avvolge tutto per schema drift. Questa è gestione corretta, non divergenza.

**Float per il denaro** — <https://www.php.net/manual/en/language.types.float.php>. Vedi REL-03.
È una divergenza reale con conseguenze reali per un sistema fiscale.

**Il ramo DEBUG di `Db::query()` stampa SQL nella pagina** (`Db.php:45`) — quando `DEBUG` è
impostato in `config/cassa.php`. Il default è `0` (`config/cassa.php:33`) quindi è spento in
produzione, e il ramo fa `htmlspecialchars()` sia della query che dell'errore, quindi non è un
vettore di injection. **Solo informational** — ma nota che un kiosk lasciato in modalità DEBUG
stamperebbe schema e testo delle query su una schermata mostrata al cliente. Vale un commento di
una riga a `config/cassa.php:33` che noti la conseguenza kiosk-display.

---

## G. Prioritized Improvement Plan (solo proposte — nulla eseguito)

### Phase 0 — Correggere l'unico difetto reale *(prima, da solo)*

**Affronta:** REL-01
**Componenti:** `src/Cassa/StampaController.php`
**Task:**

1. Scopare la query a riga 98 per `id_cassa` e `chiuso`, seguendo il pattern già corretto a
   riga 32.
2. Scopare la query di `Fiscale::emettiScontrino` per `id_cassa` (o passare l'id verificato dal
   caller invece di accettarlo grezzo).
3. Aggiungere un check lato server che l'ordine sia stato effettivamente stampato da questo
   terminale — `num_biglietti` impostato a `StampaController.php:89` — prima di consentire il
   ramo `stampato`.
4. Valutare di richiedere POST + CSRF per il ramo che esegue l'emissione.

**Dipende da:** niente.
**Rischio:** Medium — cambia il flusso di pagamento dal vivo. Mitigare scopando solo le query
(step 1-2 sono strettamente restringenti e non possono rompere il caso legittimo
single-terminal) e consegnando lo step 3 separatamente.
**Verifica:** walkthrough manuale del kiosk (single terminal), più un nuovo test che asserisca
che un `id_ordine` di un `id_cassa` diverso non produca righe né emissione.
**Beneficio:** chiude l'unico percorso通过 il quale record fiscali o totali cross-terminal
possano essere manipolati.

---

### Phase 1 — Cancellare il codice morto *(miglior valore per riga toccata)*

**Affronta:** REL-02, INFO-01
**Componenti:** `functionsFrontend.inc`, `tests/Unit/CassaViewTest.php`,
`src/Config/FestaConfig.php`
**Task:**

1. Cancellare le 17 funzioni morte e i loro doc-comment (§C.2).
2. Rimuovere gli stub `eval()` a `CassaViewTest.php:70-86` per i nomi ora assenti.
3. Cancellare `FestaConfig.php` (nessun caller).
4. Riscrivere l'header del file (nota `T31` a riga 3) per riflettere lo stato reale attuale.

**Dipende da:** niente (farla prima della Phase 2 così il prossimo refactor parte da codice pulito).
**Rischio:** Low — cancellazione pura, zero caller verificati. `PowerF2Test.php` usa
`#[RunTestsInSeparateProcesses]` in parte *proprio a causa* delle collisioni tra stub; rimuovere
gli stub potrebbe permettere di togliere quell'attributo (opzionale, differito).
**Verifica:** `php -l functionsFrontend.inc`; l'intera suite `phpunit` deve restare a 149 verdi;
`phpcs` deve restare a 0 errori.
**Beneficio:** −171 righe, −9 contratti falsi, la suite perde il suo scaffolding `eval()`.

---

### Phase 2 — Completare lo split vista già in corso

**Affronta:** MAINT-02
**Componenti:** `functionsFrontend.inc` (5 emitter), `src/Cassa/OrderService.php`,
`src/Stats/StatsData.php`
**Task:**

1. Per ognuno dei 5 emitter che portano SQL, spostare la query nella casa appropriata già
   esistente — letture ordine/carrello → `OrderService` (`$db` iniettato via costruttore, come
   dal suo design attuale), letture standby → `StatsData`.
2. Lasciare l'emitter come echo puro, **esattamente** come le 4 sorelle già migrate.
3. **Non** introdurre una nuova classe. Si completa la convenzione del progetto, non se ne inventa
   una.

**Dipende da:** Phase 1 (così il diff è su codice pulito).
**Rischio:** Low-Medium — tocca il rendering. Mitigare scrivendo prima in un test golden
l'output HTML dell'emitter, così i byte sono fissati *prima* di spostare la query. Il progetto
usa già questa tecnica (test golden in `tests/Unit/LabelBuilderTest.php`, commit `877bad2`).
**Verifica:** test golden per i 5 frammenti; suite completa verde.
**Beneficio:** elimina il motivo per cui la suite ha bisogno di stub `eval()`; rende il file
internamente coerente.

---

### Phase 3 — Separare l'I/O di stampa etichette

**Affronta:** REL-04
**Componenti:** `src/Cassa/LabelBuilder.php`, `src/Cassa/PrintService.php`
**Task:**

1. `generaCardDegustazione()` ritorna la sua stringa label.
2. Spostare la scrittura `storage/labelCards` + l'invocazione `lpr` in `PrintService` (che già
   possiede spool-and-send).
3. Instradare il path attraverso `Storage::path()` per la guardia traversal.
4. Aggiungere un timeout alla chiamata `lpr`.
5. Sostituire il `die()` grezzo a `LabelBuilder.php:292` con un valore di ritorno gestito dal
   caller.

**Dipende da:** Phase 1 raccomandata prima (stessa famiglia di file), non strettamente
richiesta.
**Rischio:** Medium — stampa fisica. Verificare su GX420t/TLP2844 in fixture prima della fiera.
**Verifica:** `LabelBuilderTest` esteso per asserire la stringa ritornata; verifica stampa manuale.
**Beneficio:** i builder puri restano puri; una stampante impiantata non può più congelare la
vendita.

---

### Phase 4 — Igiene di naming e commenti *(opportunistica)*

**Affronta:** SEC-01, MAINT-01, INFO-01
**Task:** rinominare `mysql_query_safe` → `db_query_raw` (9 siti); rimuovere la normalizzazione
barcode tripla (2 siti); notare la conseguenza DEBUG/kiosk a `config/cassa.php:33`; opzionalmente
rinominare `VisualizzaStore`.

**Dipende da:** Phase 1.
**Rischio:** Molto low — tutto meccanico, tutto verificato dal compilatore.
**Verifica:** lint + suite.

---

### Phase 5 — Precisione del denaro *(pianificare deliberatamente, non di fretta)*

**Affronta:** REL-03
**Componenti:** `database/migrations/0004_decimal.sql`, `src/Cassa/OrderService.php`,
`src/Fiscale/Fiscale.php`
**Task:** migrazione schema a `DECIMAL`; aritmetica in centesimi interi o BCMath; `Fiscale::buildXml`
deriva i centesimi senza binary float.

**Dipende da:** Phase 0-4 complete e stabili. **Richiede** prima di esportare un confronto dei
totali vecchi vs nuovi su un dataset di fiera completo — se il diff è vuoto su dati reali, il
rischio è molto più basso.
**Rischio:** High rispetto alla sua dimensione — è il percorso più critico dell'app. Non
consegnare senza il confronto dati.
**Verifica:** confronto dei totali su dataset completo; ri-eseguire `LabelBuilderTest`,
`FiscaleCodaRetryTest`, `StatsDataContatoriTest`, e una vendita completa manuale.
**Beneficio:** elimina la possibilità di un centesimo sbagliato su un documento fiscale.

---

### Esplicitamente NON raccomandato

- **Non spazzare ulteriormente `functionsFrontend.inc`.** Dopo Phase 1-2 è su ~450-550 righe con
  una responsabilità dichiarata ("adapter HTTP cassa + emitter di vista") e un motivo documentato
  per cambiare come unità. Un ulteriore split creerebbe file senza motivo indipendente per
  cambiare.
- **Non migrare a un framework.** Nessuna evidenza lo supporta; `src/` è già pulito e stratificato.
- **Non introdurre interfacce o un DI container.** Il progetto ha deciso esplicitamente contro
  (`ARCHITETTURA_REVISTA.md:506-520`) e quella decisione è corretta per questa scala.
- **Non riscrivere i builder di `LabelBuilder.php` in classi.** Il commento dell'header (riga 8)
  ha ragione: funzioni pure sono la forma corretta per builder puri, e `PureBuildersTest.php` li
  copre bene.
- **Non inseguire i 265 warning PHPCS.** `phpcs.xml` li tratta deliberatamente come non bloccanti
  per evitare un reformat di massa di codice di rendering byte-pinned.

---

## H. Verification Recommendations

**Già eseguiti in questo audit** (tutti sola lettura):

| Check | Comando | Risultato |
|---|---|---|
| Sintassi, tutti i 76 file non-vendor | `php -l` | **0 fallimenti** |
| Suite di test | `vendor/bin/phpunit` | **149 verdi**, 454 asserzioni, 2 warning |
| Standard di codice | `vendor/bin/phpcs --report=summary` | **0 errori**, 265 warning, 30 file |

`php` non è nel `PATH` in questo ambiente; il binario è `C:\xampp\php\php.exe` (8.2.12,
corrisponde a `composer.json`).

**Non eseguiti, e perché:** non ho eseguito l'applicazione contro un database vivo, non ho
connesso stampante o dispositivo fiscale, non ho esercitato alcun path hardware. `phpunit` gira
con finte ovunque. L'ispezione statica non può stabilire il comportamento reale del kiosk.

**Per una futura fase di implementazione:**

1. **Baseline prima di cambiare qualsiasi cosa.** Ri-eseguire lint + suite + phpcs e registrare
   i numeri. La suite è il gate di regressione: deve restare 149/149 verde in ogni fase.
2. **Golden-file del rendering prima di spostarlo (Phase 2).** Il precedente del progetto è il
   commit `877bad2` ("test golden"). Catturare l'HTML esatto dei 5 frammenti prima di toccare le
   loro query. `CassaViewTest` asserisce già diverse stringhe esatte.
3. **Phase 0 richiede un test kiosk manuale.** Nessun test automatico può coprirlo. Scenario
   minimo: su un singolo terminale, completare una vendita end-to-end e confermare che lo
   scontrino fiscale sia emesso esattamente una volta con il totale corretto.
4. **Test negativo per Phase 0 (da aggiungere alla suite):**
   `?action=s&stampato=<id_ordine di un altro id_cassa>&metodo=contanti` deve produrre nessuna
   riga, nessuna emissione, nessun totale cross-terminal. Il pattern `FakeMysqli` di
   `CassaControllerTest` è direttamente riusabile.
5. **Phase 3 richiede hardware.** `CartaStampaF3Test` e `PrinterF4GateParserStatoTest` coprono
   la logica pura, ma il cambio di timeout della `lpr` e la rilocazione di labelCards devono
   essere verificati sulla stampante reale.
6. **Phase 5 richiede un diff sui dati.** Esportare i totali dal sistema corrente su un dataset di
   fiera completo, passarli attraverso la nuova aritmetica, e richiedere una corrispondenza esatta
   prima del deploy.
7. **Scenari di regressione critici per la cassa** da verificare manualmente prima di considerare
   completata qualsiasi fase:
   - Nuovo ordine → aggiungi prodotti → modifica quantità su/giù → rimuovi riga → totali corretti
     a ogni passo.
   - Standby → riattiva → totali intatti.
   - Annulla ordine → carrello genuinamente vuoto, nessuna riga orfana
     (`SELECT * FROM righe_ordini WHERE id_ordine NOT IN (SELECT id_ordine FROM ordini)`).
   - Carta continua vs biglietti singoli → contenuto label, comportamento del taglio, visibilità
     di `taglia`.
   - Stampante irraggiungibile → l'ordine resta aperto, il retry funziona, nessuna stampa
     duplicata.
   - Dispositivo fiscale spento → la vendita prosegue, banner mostrato, ordine in coda, la coda
     drenata al recupero (`FiscaleCodaRetryTest` copre la logica di drain).
   - Scansione barcode di un codice sconosciuto → banner visibile, nessuna mutazione d'ordine.
   - Doppio tap rapido su un pulsante prodotto → la quantità incrementa una volta per tap, i
     totali restano consistenti.

---

## I. Open Questions and Limitations

**Genuinamente non verificati (l'ispezione statica non può risolverli):**

1. **`0003_innodb.sql` è effettivamente applicata sul DB di fiera?** Il repository contiene la
   migrazione e il codice dipende da essa (i commenti di `OrderService` ripetono "effettivo su
   InnoDB da T21, no-op su MyISAM baseline"). Se il DB vivo è ancora MyISAM, **tutte le
   transazioni in `OrderService` sono no-op silenziosi** e `SELECT ... FOR UPDATE` non blocca
   nulla. Questo cambierebbe materialmente la severity di REL-03 e invaliderebbe il design di
   concorrenza a `OrderService.php:196-198`. **È la domanda aperta a più alto valore
   dell'audit.** Richiede un `SHOW TABLE STATUS` sul DB reale.
2. **Versione PHP sul target di deploy effettivo.** `composer.json` richiede `^8.2` e il locale è
   8.2.12, ma la versione Apache/PHP del laptop di fiera non è stata verificata dal repository.
   Se è 8.1, l'assunzione sul default eccezioni di mysqli tiene comunque (8.1+); se fosse 8.0, i
   blocchi `catch (\Throwable)` non scatterebbero mai e la gestione errori degraderebbe in
   silenzio a ritorni `false`.
3. **Concorrenza multi-terminale nella pratica.** Lo schema supporta `id_cassa` 1-999. Se due
   cassieri condividono mai un database nella stessa fiera — che è precisamente quando lo scope
   mancante di REL-01 e il locking `FOR UPDATE` contano di più — non è determinabile dal
   repository.
4. **Distribuzione reale dei prezzi.** L'impatto pratico di REL-03 dipende da se i prezzi attuali
   siano valori che il binary float rappresenta pulitamente. Non determinabile senza dati di
   produzione (e ispezionare il DB di produzione è fuori scope).
5. **Correttezza di `.env` sul laptop di fiera.** Presente nel working tree (367 byte),
   gitignored, contenuto non ispezionato (credenziali). `CassaConfig::carica()` farebbe
   `die(500)` se mancasse l'hash admin o l'IP stampante, quindi l'app si auto-diagnostica — ma
   non è stato confermato che avvii con la config reale.

**Esplicitamente fuori scope / non investigate:**

- `resources/EPL2_Manual.md` e `resources/ZPLII-Prog.md` — reference vendor del linguaggio
  stampante, letti solo per contesto.
- `public/style.css` — 31 righe non committate presenti all'avvio dell'audit; non ho analizzato il
  foglio di stile perché non è architetturale. Quelle modifiche non committate, più le due diff in
  `reserved/`, sono lavoro preesistente che va preservato.
- `.idea/` — configurazione IDE.
- `docs/DATABASE_SCHEMA.md` — referenziata dalle migrazioni; non necessaria per le conclusioni di
  questo audit.
- Se `Fiscale::ritentaCoda()` venga invocata fuori dal path di drain di `emettiScontrino()` — non
  ho trovato cron né entry point separato, quindi il retry della coda sembra avvenire
  opportunisticamente alla prossima emissione riuscita. Se la coda dovesse drenare su schedule,
  questo potrebbe essere un gap, ma non ho trovato evidenza che un tale meccanismo sia mai stato
  previsto.

**Dichiarazione di conformità:** Nessun file è stato creato, modificato, cancellato o spostato.
Nessuna operazione Git che alterasse working tree, indice o cronologia è stata eseguita. Nessuna
dipendenza installata, nessuna configurazione cambiata, nessun database o sistema esterno
toccato.