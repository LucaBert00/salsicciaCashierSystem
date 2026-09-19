# Analisi legacy per Gruppo 5 — fiscale / statistiche / export

Fonte: `C:\xampp\htdocs\salsiccia\1.0` + `C:\xampp\htdocs\salsiccia\DB`.
Destinazione: mappa [Wayfinder Map] Gruppo 5 #87 (messa in standby per questa sessione).
Data: 2026-09-16. Branch Stagisti: `master` (ultimi commit: Closes #97, #92, #88).

Nota di stato: il body della mappa #87 mostra `[ ]` su #92/#97 ma il contatore
`sub-issues-completed: 4/13` e il git log (Closes #88, #92, #97 + research #89)
dicono che il blocco fiscale e' chiuso. Il body va riallineato, non e' un
lavoro mancante. Resta aperto tutto il blocco statistiche (#90, #93, #94, #95,
#98) + export (#91, #96, #99) + verify #100.

Ogni affermazione e' tracciata a `file:riga` sul legacy. Percorsi sotto sono
relativi a `C:\xampp\htdocs\salsiccia\1.0` salvo diversa indicazione.

---

## 1. `vendor/` — una sola dipendenza reale

- `vendor/` contiene **solo** `szymach/c-pchart` (+ `autoload.php` Composer 2.3,
  guard PHP < 5.6). Niente altro.
- Versione pinnata da `grafici/composer.json:2-4` + `grafici/composer.lock`:
  **v3.1.1** (2025-10-22), `php ^8.0`, `ext-gd`, licenza **GPL-3.0-only**,
  autoload `CpChart\ -> src/`, font in `vendor/szymach/c-pchart/resources/fonts/`
  (Forgotte.ttf usato dal codice, + Silkscreen, pf_arma_five, verdana, calibri…),
  doc per-chart in `resources/doc/` (2d_pie, 3d_pie, bar, stacked_bar…),
  test codeception in `tests/unit/` (PieTest incluso).
- Usato in **un solo punto produttivo**: `stat.php:198-248`
  (`\CpChart\Data` + `\CpChart\Image(820,600)` + `\CpChart\Chart\Pie->draw3DPie`
  con `LabelMode 0` esterno, `PIE_VALUE_BOTH`, font Forgotte 14, titolo
  "Riepilogo Vendite"). Demo separata in `3dpie.php:1-117` (tre torte demo su
  dati finti A/B/C, mai chiamata dal flusso).
- Implicazioni per #90/#98: ext-gd obbligatoria sullo XAMPP fiera; licenza
  GPL-3.0-only al seguito (va bene per uso interno fiera, da sapere se mai si
  distribuisce); nessun vincolo di rete (render server-side, a differenza di
  statJS, vedi §2).

## 2. `grafici/` — QUATTRO generazioni di chart una sopra l'altra, mai pulite

| Generazione | Dove sta | Usata da | Stato |
|---|---|---|---|
| libchart 2011 (`grafici/libchart/`, ChangeLog+COPYING+README) | `grafici/libchart/classes/` | `grafico.php:25` (demo dati finti browser Mozilla/Konqueror/Opera…), `stat_pdf.php:3`, `funzioni.inc:482` (PieChart 840x500), `funzioni.inc:580` (VerticalBarChart 840x500) | **in uso dal flusso PDF** |
| libchart-1.3 (`grafici/libchart-1.3/` + `libchart-1.3.tar.gz` 106KB) + `libchart_old/` | idem | nessuno (copie morte) | da cancellare |
| JpGraph bundle (`grafici/jpgraph/`, ~3.7MB, 54 file: pie, pie3d, bar, gantt, radar, Examples/, fonts/, lang/, themes/) | `grafici/jpgraph/jpgraph*.php` | **ZERO riferimenti in tutti i `*.php`/`*.inc`** (grep vuoto) | valutato e abbandonato, da cancellare |
| c-pchart via Composer (`../vendor`) | `vendor/szymach/c-pchart/src/` | `stat.php:198-248`, demo `3dpie.php` | in uso dal flusso video |
| Chart.js via CDN | stampato da `statJS.php:229` (`cdn.jsdelivr.net/npm/chart.js`, canvas `#graficoTorta`, dati via `json_encode`) | `statJS.php:224-259` | richiede internet in fiera = fragile |

Dettagli che contano per i ticket:

- `stat.php:112-265` e `statJS.php:112-265` sono **lo stesso file duplicato**:
  stesso form (date+ore+ordinamento+checkbox categorie), stessa query
  (`stat.php:152-156`, `statJS.php:168-172`), stessa tabella HTML. Differiscono
  solo nel rendering: server-side c-pchart vs client-side Chart.js. Il blocco
  libchart in `statJS.php:156-166` e' commentato. Tenere due copie allinea i bug
  due volte: il Decide #90 deve sceglierne UNA.
- `stat.php:241-248` scrive su **file condiviso fisso** `grafici/torta.png`
  (unlink + render + `<img src>`). Due casse in concorrenza si sovrascrivono
  (stessa race che la mappa cita per `torta.png`, confermata nel codice).
- Il flusso PDF invece scrive per-categoria/per-giorno
  (`funzioni.inc:563` → `grafici/torta_$id_cat-$grapg_index.png`,
  `funzioni.inc:639` → `grafici/barre_$i.png`) e gli orfani in
  `grafici/` lo provano: `torta_14-0/14-1/14-2, 16-2/16-3, 19-3/19-4, 3-0/3-1,
  barre_0/barre_1.png`. Sono residui di run reali, mai puliti, dentro docroot e
  servibili via HTTP.
- `grafico.php:27-42` e' solo demo libchart con dati finti (browser anni 2000):
  nessun valore produttivo, confonde la ricerca.

## 3. Statistiche — query esistenti da parametrizzare (#93) + KPI dimenticati

### 3.1 Aggregato principale (stat.php / statJS.php)

`stat.php:152-156` (identica in `statJS.php:168-172`):

```sql
SELECT prodotti.descrizione_prod AS prodotto,
       sum(righe_ordini.totale) AS tot, sum(quantita) AS quantita
FROM ordini, righe_ordini, prodotti, prodotti_categorie
WHERE ordini.id_ordine = righe_ordini.id_ordine
  AND prodotti.id_prodotto = righe_ordini.id_prodotto
  AND prodotti_categorie.id_prodotto = prodotti.id_prodotto
  AND ordini.data_ora BETWEEN '$d_i' AND '$d_f' $where_cat
GROUP BY prodotti.id_prodotto $ord
```

con `$d_i/$d_f` da POST data+ora (`stat.php:114-121`), filtro categorie da loop
**hardcoded `cat_1..cat_7`** (`stat.php:126-138`), ordinamento da POST
`ord` (`@`/prod/quant/prez → ORDER BY, `stat.php:140-150`). Tutto interpolato
in chiaro: SQL injection aperta su date, categorie e ordinamento — il Task #93
(aggregate parametrizzate) ha qui il punto di partenza esatto.

### 3.2 Variante PDF (funzioni.inc, usata da stat_pdf.php)

- `print_tabGraf_ordini` (`funzioni.inc:477-575`): loop su
  `SELECT * FROM categorie`, per categoria aggregato per prodotto con filtro
  aggiuntivo `tipo = TIPO_ORDINE` (`funzioni.inc:518-522`, `TIPO_ORDINE=nor` da
  `set.inc`) + totali per categoria (`funzioni.inc:541-547`) + torta libchart
  per categoria. Le righe commentate `funzioni.inc:483-495` mostrano esclusioni
  storiche di categorie (17/18) — logica evento-specifica fossilizzata.
- `print_tabGraf_affluenza` (`funzioni.inc:577-648): statistica oraria
  `SELECT date_format(data_ora,"%H") ora, sum(totale), sum(num_biglietti),
  count(id_ordine) … GROUP BY ora` (`funzioni.inc:584-586`) + totali
  (`funzioni.inc:616-617`) + media biglietti/ordine + barre libchart
  (`funzioni.inc:637-646`).
- `print_tabGraf_totali` (`funzioni.inc:650-739`): totali generali evento,
  gestione `TOTAL_LABEL` (`funzioni.inc:684-690`), **matematica rotoli**:
  967 etichette/rotolo, `n_rotoli = ceil(tot_pezzi/967)`
  (`funzioni.inc:729-737`). Il 967 e' una costante fisica della carta, oggi
  sepolta nella funzione: se #90 tiene i totali, merita una costante con nome.
- Bug latente: `print_tabGraf_totali($pdf,$d_i,$d_f)` non dichiara `$mysqli`
  ma `funzioni.inc:674` lo usa — funziona solo per fallback globale/warning
  soppresso. Da sistemare se riusata.

### 3.3 KPI leggeri che la mappa non cita (candidati per il Decide #90)

- `tot.php:123-137`: **due numeri** (`sum(totale)`, `sum(num_biglietti)` con
  `chiuso=1` + BETWEEN). E' il KPI minimo gia' pronto, zero chart, zero PDF.
- `srtc.php:35-43`: realtime (refresh 10s) sui `contatori` con `attivo_app='T'`.
- `conta.php:34` + `funzioni.inc:907-937` (`conta_prodotto`): totale per
  contatore = `SUM(quantita)*qta` sui prodotti collegati, con opzionale filtro
  periodo (`controllo_periodo T/F`, `data_da/a`). `stampa_contatori`
  (`funzioni.inc:896-905`) li espone come bottoni in `config.php:70`.
- Tabelle `contatori` + `prodotti_contatori` definite in
  `1.0/reserved/db.inc:80-133` e presenti nei dump `DB/*.sql` ( righe 54, 543):
  nome, limite_qta, controllo_periodo, data_da/a, attivo, **attivo_app**.
  Esempio reale: `contatori` → `('Bocc', limite 100, …, attivo T, attivo_app T)`.
  Sono conteggi promozionali/ingredienti (brasato, polenta, taragna, spiedini nei
  commenti di `srtc.php:45-70`, `config.php:74-81`) — riusabili come KPI fiera
  senza chart.

### 3.4 Form e accesso

`stat.php:268-317`, `statJS.php:268-317`, `tot.php:155-192`: form data/ora con
calendario `calendario/` + `printOre()` (`funzioni.inc:441`), checkbox categorie
da `SELECT * FROM categorie`. Nessun controllo auth sui file stat/tot/stat_pdf:
li protegge solo il fatto che `config.php:125-128` mostra i bottoni
Statistiche/Totali/Riepilogo solo con `code == PWD."@0*"`. URL diretta = accesso
libero (voce per #100).

## 4. Fiscale — protocollo confermato, due gap (UI morta, schema)

`stampaScontrini.php:27-127` e' il riferimento legacy del protocollo, e combacia
con quanto gia' reimplementato in Stagisti `fiscale.inc`:

- Aggregazione per IVA: `stampaScontrini.php:33-35`
  (`sum(totale), sum(qta), iva … GROUP BY iva` con `id_ordine` da `$_GET[id]`
  interpolato — injection, in Stagisti e' prepare in
  `fiscale_emetti_scontrino`), mapping `0.04→=R1, 0.10→=R2, 0.22→=R3` in
  centesimi `round(tot*100)` (`stampaScontrini.php:50-64`), chiusura `=T3` carte
  se `mod==cc` else `=T1` contanti (`stampaScontrini.php:67-70`), apertura `=K`,
  envelope `<Service>` (`stampaScontrini.php:42-71}).
- Trasporto: POST form `data=$xml` a `http://192.168.0.210/service.cgi`
  hardcoded (`stampaScontrini.php:81-83`), **senza timeout**, risposta
  `SimpleXMLElement->Request->{errorCode,printerError,paperEnd,coverOpen,
  lastCmd,busy}` (`stampaScontrini.php:92-99`), messaggi per errore
  (`stampaScontrini.php:103-124`). Niente fallback/coda/retry/log/mock/
  idempotenza: tutto cio' esiste solo in Stagisti (`fiscale.inc:119-208`).
  Il legacy stampava anche l'XML in `<textarea>` di debug
  (`stampaScontrini.php:73`): non replicare.

Gap trovati:

1. **Percorso morto nella UI**: in `stampa.php:186-187` i bottoni
   cash/cc verso `stampaScontrini.php?action=p…` sono **commentati**. Dopo la
   stampa etichette + `ftpPut()` + `UPDATE ordini SET chiuso=1`
   (`stampa.php:109-117`) resta solo il calcolatore resto
   (`stampa.php:168-199`, immagini in `soldi/` — oggi `asset/soldi/` in
   Stagisti). Il fiscale non e' raggiungibile dal flusso cassa legacy.
2. **Schema**: `1.0/salsiccia.sql:110-117` (dump 2012) **non ha la colonna
   `iva`** in `prodotti`; i dump `DB/salsiccia.sql:291-305`,
   `DB/salsiccia_capraSI.sql:314-328` e `DB/salsiccia(1).sql` **ce l'hanno**
   (`iva float NOT NULL`, valori reali `0.22`, piu' colonna `barcode` con EAN).
   Anche `contatori`/`prodotti_contatori` esistono solo nei dump `DB/` (righe
   51-69, 540-553), non nel dump 2012. Il fiscale dipende quindi da una
   migrazione mai documentata in repo: in Stagisti non c'e' alcuno `.sql`,
   rischio deploy per la prova ripristino #99 (ripristinare *cosa*, su quale
   schema?).

## 5. Export/backup — base zero, due file ingannevoli (#91/#96/#99 greenfield)

- `reserved/export.php:1-48`: legge `clienti_export.inc` e
  `clienti_ditte_export.inc` (file **inesistenti**), tabelle `clienti` /
  `clienti_ditte` (**inesistenti** in ogni dump), link a `sync.php` (**file
  inesistente**). Copia morta da altro progetto: da cancellare, non da
  riparare. Grep `mysqldump|backup|dump` su `1.0/` = vuoto: nessun backup mai
  esistito.
- `1.0/ripristino.php:1-47`: nonostante nome e titolo "Recupero Database",
  esegue `REPAIR TABLE` su tutte (`SHOW TABLES` loop, righe 10-22) con credenziali
  hardcoded (`localhost/salsiccia/Salsiccia@123`, righe 4) e stampa "Tabella X
  **copiata**" (parola sbagliata, riga 16). E' manutenzione MyISAM, non restore.
  Da sostituire, non da riusare — e attenzione a non confonderlo con un vero
  ripristino in fiera.
- `pdf/` = archivio storico reale: **~100 PDF di eventi 2010-2024** (~12MB:
  `Capra2019.pdf`, `Zootecnica2019.pdf`, `FestaCapra2021.pdf`…), piu' FPDF
  2012 vendored (`pdf/fpdf.php` + `fpdf.php.old`, `pdf/font/`, `pdf/tutorial/`).
  `stat_pdf.php:101` scrive `pdf/stat.pdf` (singolo, **in docroot**,
  sovrascritto a ogni run) e `pdf_cucina/` contiene 33 `report_cucina-*.pdf`
  2012 + `Old/`. Per #95: l'anti-pattern da non replicare e' output in docroot;
  per #96: l'`.htaccess` Stagisti nega gia' `salsiccia.sql|label|.env` ma
  **non** `pdf/stat.pdf`, `grafici/*.png`, `fiscale_*.log` — voce per #100.
- Dettagli `stat_pdf.php` se riusato: usa `$HTTP_POST_VARS` (rimosso in PHP8,
  `stat_pdf.php:42`, `tot.php:112` usa ancora `$HTTP_POST_VARS` contro
  `stat.php:112` che usa `$_POST` — migrazione parziale), include
  `grafici/libchart/classes/libchart.php` (`stat_pdf.php:3`), debug a video
  `PieChart TROVATA!!!` (`stat_pdf.php:12-15`), ciclo giorni su
  `DURATA_FESTA`+`ORA_CAMBIO_DATA` (`stat_pdf.php:65-89`).

## 6. Note per #100 (verify) e_auth

- `config.php:33-136`: due livelli via GET `?code=`: `code==PWD` → gestione
  (contatori, printer reset/setup, reboot/halt via `cmd.php`/`spegni.php`,
  stampe cucina, switch DB `dbSelect.inc` tra DB0/DB1) ; `code==PWD."@0*"` →
  statistiche. PWD default `@123*` (oggi env `SALSICCIA_ADMIN_PWD` in Stagisti
  `set.inc:96`). Segreto in URL = finisce in log/history: se #90/#93 espongono
  pagine stat, serve il login di `reserved/` (Stagisti ha gia'
  `reserved/login.php` + `visualizza.php`), non il code-in-URL.
- `set.inc` legacy legge il DB attivo da `dbSelect.inc` su filesystem
  (`set.inc:9-15`, formato `DB0#DB1`, switch in `config.php:92-123`) con
  `MULTIPLE_DB`, `DB0 ProtCivCrem19`, `DB1 CoroValsass19`, `EVENT_NAME` a deploy
  (`set.inc:23-27`, oggi INFINITY 90 Casargo 18/07/2026). Stagisti ha gia'
  semplificato (decisione #55: costante + deploy). Il file `dbSelect.inc` fuori
  Stagisti non va ricreato.
- `cmd/cmd`, `labels/*` (cmd_set_label, continous_media_GX420T, zpl_set…),
  `print.sh`, `printer.inc` (2 byte): archeologia stampanti, utile solo come
  riferimento comandi ZPL/EPL.

## 7. Proposte di nuova direzione per la mappa

1. **#90 Decide — allargare le opzioni**: oltre a KPI+SVG-vs-lib+PDF, decidere
   (a) riuso `tot.php` come KPI minimo (2 numeri, pronto), (b) contatori/srtc
   come KPI realtime senza chart, (c) divieto Chart.js-CDN in fiera (offline),
   (d) esito per JpGraph (drop: 3.7MB morti) e copie libchart morte, (e) dove va
   la costante 967 etichette/rotolo. Opzione server-side c-pchart resta valida
   ma con output per-richiesta, mai file condiviso.
2. **#93 Task — punto di partenza pronto**: parametrizzare i 4 shape di §3.1,
   §3.2 (ordini per prodotto/categoria, affluenza oraria, totali, contatori);
   loop `cat_1..7` → dinamico da `categorie`; validare date BETWEEN.
3. **#94/#98 — vincolo output**: SVG per-richiesta (niente `torta.png`
   condiviso); orfani `grafici/torta_*-*.png`, `barre_*.png` da non ricreare e
   da rimuovere dal repo quando il nuovo flusso li sostituisce.
4. **#95 — condizionale, base esistente**: FPDF e' gia' vendored (`pdf/fpdf.php`
   2012) + 100 PDF storici come fixture di formato; output obbligatorio fuori
   docroot (`pdf/stat.pdf` in docroot e' l'anti-pattern).
5. **#91/#96/#99 — greenfield totale**: definire cosa (dump di quali tabelle?
   schema con `iva`+`barcode`+`contatori` dei dump `DB/`, non del dump 2012),
   quando (fine-giornata), chi, dove (fuori docroot + supporto esterno),
   ripristino = vera import di prova (mai `REPAIR TABLE` di `ripristino.php`);
   cancellare `reserved/export.php` morto; estendere `.htaccess` a
   `pdf/stat.pdf`, `grafici/*.png`, `fiscale_*.log` finche' restano in docroot.
6. **Precondizione schema**: nessuna statistica fiscale/contatori gira sul dump
   `1.0/salsiccia.sql` (manca `iva`, mancano `contatori`): il deploy fiera deve
   partire dai dump `DB/` — da fissare in #91/#99, non dato per scontato.
