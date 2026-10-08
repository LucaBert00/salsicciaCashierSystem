<?php
declare(strict_types=1);
// Shim di delega (F5.5a #117, docs/ARCHITETTURA_REVISTA.md §10 punto 20-parte shim).
// Nessuna logica qui dentro: ogni globale e' un one-liner verso il metodo statico
// equivalente gia' esistente nei moduli F5 (F5.1 #103 src/Support/{Env,Storage,
// Session,Throttle}, F5.2 #104 src/Printer/{PrinterRegistry,PaperSetup}, F5.3 #105
// src/Printer/CupsState, F5.4 #106 src/Config/CassaFlags). Corpi verbatimvivono
// li'; i 28 file che chiamano i globali restano invariati (migrazione caller = #118-#121).
// Delete del file solo quando l'ultimo consumer sara' migrato (F5.5b, #107).
//
// T24: unica facade log (livello sopra error_log, PSR-3 dopo). Solo codici, mai payload.

use Salsiccia\Config\CassaFlags;
use Salsiccia\Printer\CupsState;
use Salsiccia\Printer\PaperSetup;
use Salsiccia\Printer\PrinterRegistry;
use Salsiccia\Support\Env;
use Salsiccia\Support\Session;
use Salsiccia\Support\Storage;
use Salsiccia\Support\Throttle;

// Le deleghe puntano a classi PSR-4: l'autoload deve esistere gia' qui, altrimenti
// dbConnect.php (che richiede env.inc) leggerebbe getenv() senza .env caricato.
require_once __DIR__ . '/vendor/autoload.php';

if (!function_exists('cassa_log'))
{
    function cassa_log(string $level, string $message): void
    {
        Env::log($level, $message);
    }
}

// Punto unico di caricamento .env (era side-effect top-level del require): una sola
// volta per processo, prima di qualunque getenv() (dbConnect.php:6-9 fail-closed,
// set.inc su SALSICCIA_PRINTER_IP/ADMIN_PWD_HASH). Precedenza alle env reali
// (sistema/Apache) invariata.
Env::carica();

// Cartella fuori docroot per spool/coda/log (T16: DocumentRoot -> public/).
function salsiccia_storage_dir(): string
{
    return Storage::dir();
}

// Path assoluto in storage/ per nomi spool/coda/log (solo basename, mai traversal).
function salsiccia_storage_path(string $name): string
{
    return Storage::path($name);
}

// T28: session hardening (unico punto di avvio sessione). Chiamare prima di
// session_start() ovunque al posto di session_start() nudo; kiosk invariato.
function salsiccia_is_https(): bool
{
    return Session::isHttps();
}

function salsiccia_session_start(): bool
{
    return Session::start();
}

// T28: throttle login server-side (file in storage/, fuori docroot) cosi'
// sopravvive al reset della sessione. Scope separati: 'reserved' (backoffice) e
// 'admin' (cassa); stessa regola ovunque: 5 fail -> blocco 60s.
function salsiccia_throttle_file(string $scope, string $ip = ''): string
{
    return Throttle::file($scope, $ip);
}

function salsiccia_throttle_leggi(string $file): array
{
    return Throttle::leggi($file);
}

function salsiccia_login_throttled(string $scope, string $ip = '', string $file = ''): bool
{
    return Throttle::throttled($scope, $ip, $file);
}

function salsiccia_login_fail(string $scope, string $ip = '', string $file = ''): void
{
    Throttle::fail($scope, $ip, $file);
}

function salsiccia_login_ok(string $scope, string $ip = '', string $file = ''): void
{
    Throttle::ok($scope, $ip, $file);
}

// Gate stampa etichette (F4.1 #51): tabella di verita' stampante/connessione/
// linguaggio usata sia dal gate in PrintService::generaFileStampa() sia dal calcolo
// di CONTINUOUS_LABEL (set.inc): stessa tabella, nessun duplicato.
function printer_gate_allowed($name, $conn, $lang)
{
    return PrinterRegistry::gateAllowed($name, $conn, $lang);
}

// Carta continua (scontrino unico) vs biglietti singoli: GX420t (DIRETTA dual) e
// ZD230 (RETE/DIRETTA ZPL) fanno scontrino unico; TLP2844/LP2844 fanno singoli.
function printer_is_continuous($name, $conn, $lang)
{
    return PrinterRegistry::isContinuous($name, $conn, $lang);
}

// F3.1 #61: setup carta continua/singoli, al toggle CAMBIA CARTA la stampante
// riceve il file della propria combo (nome/connessione/linguaggio, gate sopra).
// File mancante/illeggibile o contenuto non valido => false + $errore esplicito
// (mostrato via htmlspecialchars in F3.4), modalita invariata.
function cartaStampaFilePerStampante($name, $conn, $lang, $continua)
{
    return PaperSetup::filePerStampante($name, $conn, $lang, $continua);
}

// Normalizza e valida il setup letto da file, mai raw non validato alla stampante:
// EPL riga q forzata a q464/q832 + riga Q, ZPL ^XA..^XZ con ^PW464/^PW832.
function cartaStampaNormalizzaContenuto($raw, $lang, $continua)
{
    return PaperSetup::normalizzaContenuto($raw, $lang, $continua);
}

// Legge il setup per la combo ($baseDir solo per i test, default
// storage/printerCommand/). Ritorna il contenuto normalizzato da inviare,
// false + $errore esplicito se mappa/file/contenuto non validi.
function cartaStampaLeggiSetup($name, $conn, $lang, $continua, &$errore, $baseDir = '')
{
    return PaperSetup::leggiSetup($name, $conn, $lang, $continua, $errore, $baseDir);
}

// F4.1 #51: taglierino, solo la GX420t lo monta. Definita qui ma NON usata
// (l'append di taglio e' F1, fuori scope).
if (!function_exists('printer_has_cutter'))
{
    function printer_has_cutter($name)
    {
        return PrinterRegistry::hasCutter($name);
    }
}

if (!function_exists('printer_taglia_permesso'))
{
    function printer_taglia_permesso($name, $conn, $lang)
    {
        return PrinterRegistry::taglioPermesso($name, $conn, $lang);
    }
}

// F4.1 #51: registry stampanti note, nomi memorizzati anche se mai collegati.
if (!function_exists('printer_known_printers'))
{
    function printer_known_printers()
    {
        return PrinterRegistry::knownPrinters();
    }
}

// F4.1 #51: selezione persistita in storage/, mai rewrite di PHP source.
// File JSON scritto dalla UI (F4.5), letto a boot. Combo non a gate => null.
if (!function_exists('printer_selection_file'))
{
    function printer_selection_file()
    {
        return PrinterRegistry::selectionFile();
    }
}

if (!function_exists('printer_leggi_selezione'))
{
    function printer_leggi_selezione()
    {
        return PrinterRegistry::leggiSelezione();
    }
}

// F4.1 #51: raggiungibilita' minima per il gate di selezione (F4.5).
// DIRETTA = coda locale sempre raggiungibile; RETE = socket TCP corto verso l'IP,
// ping fallito gestito senza fatal (anche su Windows senza CUPS).
if (!function_exists('printer_ping'))
{
    function printer_ping($ip, $port = 21, $timeout = 2)
    {
        return CupsState::ping($ip, $port, $timeout);
    }
}

if (!function_exists('printer_is_reachable'))
{
    function printer_is_reachable($name, $conn, $ip = '')
    {
        return CupsState::isReachable($name, $conn, $ip);
    }
}

// F4.2 #52: stato CUPS veritiero, solo helper (nessun uso in gate/UI: quello e'
// F4.3/F4.5, fuori scope). Nome coda CUPS reale: oggi identita' del nome logico.
// Mai fatal senza CUPS: shell/exec con fallback come upstream.
if (!function_exists('printer_cups_queue'))
{
    function printer_cups_queue($name)
    {
        return CupsState::cupsQueue($name);
    }
}

if (!function_exists('printer_lpstat_bin'))
{
    function printer_lpstat_bin()
    {
        return CupsState::lpstatBin();
    }
}

if (!function_exists('printer_lpstat_parse_default'))
{
    function printer_lpstat_parse_default($righe)
    {
        return CupsState::lpstatParseDefault($righe);
    }
}

if (!function_exists('printer_lpstat_snapshot'))
{
    // Snapshot `lpstat -t` riusato da tutte le code a request: una sola exec.
    function printer_lpstat_snapshot()
    {
        return CupsState::lpstatSnapshot();
    }
}

if (!function_exists('printer_lpstat_parse_stato'))
{
    function printer_lpstat_parse_stato($name, $righe, $predefinita)
    {
        return CupsState::lpstatParseStato($name, $righe, $predefinita);
    }
}

if (!function_exists('printer_lpstat_p_snapshot'))
{
    // Conferma stato via `lpstat -p`: la base resta `lpstat -t`; se `-p` dice
    // disabled/unknown (o la coda manca) vince il rosso anche con `-t` idle.
    function printer_lpstat_p_snapshot()
    {
        return CupsState::lpstatPSnapshot();
    }
}

if (!function_exists('printer_lpstat_p_stato'))
{
    function printer_lpstat_p_stato($queue)
    {
        return CupsState::lpstatPStato($queue);
    }
}

if (!function_exists('printer_lpstat_applica_conferma_p'))
{
    function printer_lpstat_applica_conferma_p($st, $conf)
    {
        return CupsState::lpstatApplicaConfermaP($st, $conf);
    }
}

if (!function_exists('printer_lpstat_stato'))
{
    // Stato veritiero di una coda locale: snapshot condiviso + parser sopra.
    function printer_lpstat_stato($name)
    {
        return CupsState::stato($name);
    }
}

if (!function_exists('printer_stato_locale_ok'))
{
    // Giudizio puro su uno stato gia' parsato (+ presenza USB gia' rilevata):
    // printing vale come ok alla pari di idle; USB assente => false (rosso).
    function printer_stato_locale_ok($st, $usbPresente = true)
    {
        return CupsState::statoLocaleOk($st, $usbPresente);
    }
}

if (!function_exists('printer_usb_presente_da_evidenza'))
{
    // Presenza USB pura da evidenza raccolta (righe `lpinfo -v`), testabile con
    // fixture; con seriale nel device atteso serve il match sul seriale.
    function printer_usb_presente_da_evidenza($nodiUsb, $righeLpinfo, $deviceAtteso)
    {
        return CupsState::usbPresenteDaEvidenza($nodiUsb, $righeLpinfo, $deviceAtteso);
    }
}

if (!function_exists('printer_usb_locale_presente'))
{
    // Presenza USB reale su questa cassa: solo `lpinfo -v`. Se `lpinfo` fallisce o
    // manca NON si conclude "assente": ci si fida della coda CUPS.
    function printer_usb_locale_presente($deviceAtteso = '')
    {
        return CupsState::usbLocalePresente($deviceAtteso);
    }
}

// T17: flag fiera persistente in storage/ JSON, mai rewrite di PHP source.
// Unico punto di verita' per MODALITA_FIERA (functionsFrontend.inc isFieraAttiva(),
// public/reserved/visualizza.php fieraAttiva(), impostaModalitaFiera()).
// Scrittura atomica tmp+rename con LOCK_EX: concorrente = last-writer-wins.
function cassa_flags_file(): string
{
    return CassaFlags::cassaFlagsFile();
}

function cassa_leggi_fiera(): bool
{
    return CassaFlags::cassaLeggiFiera();
}

function cassa_imposta_fiera($valore): void
{
    CassaFlags::cassaImpostaFiera($valore);
}

// F0.3: info festa persistenti in storage/festa.json, mai rewrite di PHP source.
// Unico punto di verita' per EVENT_NAME/DURATA_FESTA (functionsFrontend.inc
// mostraModificaInfo(), PrintService, StatsData, stat_pdf; festa_imposta()).
function festa_file(): string
{
    return CassaFlags::festaFile();
}

function festa_leggi(): array
{
    return CassaFlags::festaLeggi();
}

function festa_imposta($eventName, $durataFesta): void
{
    CassaFlags::festaImposta($eventName, $durataFesta);
}
?>
