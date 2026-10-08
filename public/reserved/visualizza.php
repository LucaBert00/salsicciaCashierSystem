<?php
declare(strict_types=1);
// public/reserved/visualizza.php — router sottile 5 tab (T20).
// Config + delete/save/edit vivono in src/Backoffice/Tabs/*Tab.php: una diff di
// un tab tocca un file. Lista/form in visualizza_view.php. Pipeline G3
// (whitelist ORDER BY + LIKE bound + LIMIT int) in VisualizzaStore, invariata.

// Avvia la sessione e blocca l'accesso diretto senza login riservato
require_once __DIR__ . '/../../bootstrap.php';
\Salsiccia\Support\Session::start();
if (empty($_SESSION['reserved_auth']))
{
    header('Location: login.php?msg=2');
    exit;
}

//carica connessione mysqli e funzioni condivise
// F6.3 #111: flag DEBUG ex set.inc via CassaConfig in Db::query, mai define() qui.
require_once __DIR__ . '/../../src/Backoffice/VisualizzaStore.php';
require_once __DIR__ . '/../../src/Backoffice/Tabs/CategorieTab.php';
require_once __DIR__ . '/../../src/Backoffice/Tabs/ProdottiTab.php';
require_once __DIR__ . '/../../src/Backoffice/Tabs/ProdottiCategorieTab.php';
require_once __DIR__ . '/../../src/Backoffice/Tabs/ContatoriTab.php';
require_once __DIR__ . '/../../src/Backoffice/Tabs/ProdottiContatoriTab.php';

use Salsiccia\Backoffice\VisualizzaStore;
use Salsiccia\Backoffice\Tabs\CategorieTab;
use Salsiccia\Backoffice\Tabs\ProdottiTab;
use Salsiccia\Backoffice\Tabs\ProdottiCategorieTab;
use Salsiccia\Backoffice\Tabs\ContatoriTab;
use Salsiccia\Backoffice\Tabs\ProdottiContatoriTab;

//Gestisce logout e riporta al form di login
if (isset($_GET['logout']))
{
    unset($_SESSION['reserved_auth']);
    session_regenerate_id(true);
    header('Location: login.php');
    exit;
}

// niente SQL da $_GET non validato: whitelist tab/OR/DIR + pagina int
//$righePerPagina -> righe per pagina della lista
$righePerPagina = 5;
// Barcode facoltativo (#47, da e6559f4): i DB storici senza
// prodotti.barcode lavorano senza; sonda fail-open, mai un blocco.
$haBarcode = VisualizzaStore::haColonna($mysqli, 'prodotti', 'barcode');
$configTabs = array(
    'categorie' => CategorieTab::config(),
    'prodotti' => ProdottiTab::config($haBarcode),
    'prodotti_categorie' => ProdottiCategorieTab::config(),
    'contatori' => ContatoriTab::config(),
    'prodotti_contatori' => ProdottiContatoriTab::config(),
);
//Dispatch delete/save/edit al controller del tab (prodotti = fallback se invalido)
$classiTab = array(
    'categorie' => CategorieTab::class,
    'prodotti' => ProdottiTab::class,
    'prodotti_categorie' => ProdottiCategorieTab::class,
    'contatori' => ContatoriTab::class,
    'prodotti_contatori' => ProdottiContatoriTab::class,
);

//Legge il tab richiesto, usa prodotti come fallback se manca o non esiste
$tabCorrente = isset($_GET['tab']) ? $_GET['tab'] : 'prodotti';
$tabValido = isset($configTabs[$tabCorrente]);
if ($tabValido)
{
    $tabConfig = $configTabs[$tabCorrente];
}

// Valida colonna di ordinamento contro la whitelist del tab, altrimenti nessun OR
$colonnaOrd = isset($_GET['OR']) ? $_GET['OR'] : '';
if (!$tabValido || !isset($tabConfig['orders'][$colonnaOrd]))
{
    $colonnaOrd = '';
}

// Accetta solo DESC esplicito, tutto il resto diventa ASC
if (isset($_GET['DIR']) && strtoupper($_GET['DIR']) === 'DESC')
{
    $direzioneOrd = 'DESC';
}
else
{
    $direzioneOrd = 'ASC';
}

// Costruisce ORDER BY solo da valori in whitelist, mai da input grezzo
if ($tabValido)
{
    $orderBySql = VisualizzaStore::resolveOrderBy($tabConfig['orders'], $tabConfig['default'], $colonnaOrd, $direzioneOrd);
}
else
{
    $orderBySql = '';
}

//Testo di ricerca ripulito e troncato a 50 char
if (isset($_GET['q']))
{
    $ricerca = $_GET['q'];
}
else
{
    $ricerca = '';
}

$ricerca = substr(trim($ricerca), 0, 50);

// Numero pagina sempre intero e minimo 1
if (isset($_GET['page']))
{
    $paginaCorrente = (int)$_GET['page'];
}
else
{
    $paginaCorrente = 1;
}

if ($paginaCorrente < 1)
{
    $paginaCorrente = 1;
}

// Messaggio di stato mostrato in lista o nel form
$messaggioStato = '';

// Ogni query sotto puo fallire per schema drift (colonna/tabella mancante):
// il catch prima dell'HTML trasforma il fatal in messaggio leggibile e la
// pagina si apre comunque, degrada solo il tab coinvolto.
try
{

//Azioni riga protette 5 tab: nuovo/modifica/elimina con prepared.
// Blocco relation applicativo come vecchio visualizza.php (MyISAM = zero FK); al posto dell'alert legacy resta in lista con messaggio. Sole classi style.css.
// Il fallback invalido va ai prodotti, come prima; gli altri tab solo su match esatto.
$slugDelete = $tabValido ? $tabCorrente : 'prodotti';
$classeDelete = $classiTab[$slugDelete];
$esitoDelete = $classeDelete::elimina($mysqli, (string)($_SERVER['REQUEST_METHOD'] ?? ''), $_POST, $_GET);
if (isset($esitoDelete['redirect']))
{
    header('Location: visualizza.php?tab=' . $esitoDelete['redirect']);
    exit;
}
if (isset($esitoDelete['msg']))
{
    $messaggioStato = $esitoDelete['msg'];
}

//$rigaInModifica contiene la riga da modificare, $tabForm il tab del form, $mostraForm decide form vs lista
$rigaInModifica = null;
$tabForm = $tabValido ? $tabCorrente : 'prodotti';

// Mostra il form su nuovo oppure su edit con chiavi complete per ogni tipo di tab
$mostraForm = isset($_GET['nuovo'])
    || (in_array($tabForm, array('categorie', 'prodotti', 'contatori')) && isset($_GET['edit']))
    || ($tabForm === 'prodotti_categorie' && isset($_GET['edit_p'], $_GET['edit_c'], $_GET['edit_pos']))
    || ($tabForm === 'prodotti_contatori' && isset($_GET['edit_c'], $_GET['edit_p']));

// Carica il prodotto da modificare, torna in lista se l'id non esiste
if ($tabForm === 'prodotti' && isset($_GET['edit']) && (!$tabValido || $tabCorrente === 'prodotti'))
{
    $rigaInModifica = ProdottiTab::caricaModifica($mysqli, $_GET, $haBarcode);
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
//Carica la categoria da modificare, torna in lista se l'id non esiste
if ($tabForm === 'categorie' && isset($_GET['edit']))
{
    $rigaInModifica = CategorieTab::caricaModifica($mysqli, $_GET);
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
// Carica il venduto da modificare, torna in lista se l'id non esiste
if ($tabForm === 'contatori' && isset($_GET['edit']))
{
    $rigaInModifica = ContatoriTab::caricaModifica($mysqli, $_GET);
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
// Carica la posizione da modificare tramite chiave tripla, torna in lista se assente
if ($tabForm === 'prodotti_categorie' && isset($_GET['edit_p'], $_GET['edit_c'], $_GET['edit_pos']))
{
    $rigaInModifica = ProdottiCategorieTab::caricaModifica($mysqli, $_GET);
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
// Carica la regola venduti da modificare tramite coppia venduto+prodotto
if ($tabForm === 'prodotti_contatori' && isset($_GET['edit_c'], $_GET['edit_p']))
{
    $rigaInModifica = ProdottiContatoriTab::caricaModifica($mysqli, $_GET);
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}

// Salva il form: valida il tab inviato, il controller ripulisce i campi e fa update o insert con prepared
if (isset($_POST['save']))
{
    $tabInviato = (isset($_POST['t']) && isset($configTabs[$_POST['t']])) ? $_POST['t'] : 'prodotti';

    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
        $classeSave = $classiTab[$tabInviato];
        if ($tabInviato === 'prodotti') {
            $esitoSave = $classeSave::salva($mysqli, $_POST, $haBarcode);
        } else {
            $esitoSave = $classeSave::salva($mysqli, $_POST);
        }
        if (isset($esitoSave['redirect']))
        {
            header('Location: visualizza.php?tab=' . $esitoSave['redirect']);
            exit;
        }
        $messaggioStato = $esitoSave['msg'];
        $tabCorrente = $tabInviato;
        $tabValido = true;
        $tabConfig = $configTabs[$tabInviato];
        $tabForm = $tabInviato;
        $mostraForm = true;
        if (array_key_exists('riga', $esitoSave))
        {
            $rigaInModifica = $esitoSave['riga'];
        }
    }
}

//Option per i form ponte/regole: nomi leggibili come in lista
// Carica solo le option del tab ponte davvero mostrato per non pesare sulla lista
$opzioniProdotti = array();
$opzioniCategorie = array();
$opzioniContatori = array();
$mappaPosizioni = array();

//Option prodotti per i due form ponte con chiave esterna verso prodotti
if ($mostraForm && ($tabForm === 'prodotti_categorie' || $tabForm === 'prodotti_contatori'))
{
    $opzioniProdotti = VisualizzaStore::opzioniProdotti($mysqli);
}

//Option categorie + occupancy griglia 01-24 solo per il form posizioni
if ($mostraForm && $tabForm === 'prodotti_categorie')
{
    $opzioniPonte = ProdottiCategorieTab::opzioni($mysqli);
    $opzioniCategorie = $opzioniPonte['categorie'];
    $mappaPosizioni = $opzioniPonte['mappa'];
}
// Option venduti solo per il form regole
if ($mostraForm && $tabForm === 'prodotti_contatori')
{
    $opzioniContatori = ProdottiContatoriTab::opzioni($mysqli);
}

// Lista: COUNT(*) + LIMIT offset,rpp — mai offset esposto in URL
// Conta il totale con la stessa WHERE di ricerca, poi fissa pagina e offset dentro i limiti
$totRighe = 0;
$totPagine = 1;
$righe = array();

if ($tabValido)
{
    // Costruisce la WHERE di ricerca con LIKE su tutte le colonne del tab
    list($where, $tipi, $vals) = VisualizzaStore::buildSearch($tabConfig['search'], $ricerca);
    list($totRighe, $totPagine, $paginaCorrente, $righe) = VisualizzaStore::fetchPagina($mysqli, $tabConfig, $orderBySql, $where, $tipi, $vals, $paginaCorrente, $righePerPagina);
}
}
catch (Throwable $e)
{
    // Schema drift (colonna/tabella mancante): niente schermo bianco, la
    // pagina si apre comunque con messaggio leggibile; degrada solo il tab.
    $dettaglio = $mysqli->error;
    if ($dettaglio === '')
    {
        $dettaglio = $e->getMessage();
    }
    elseif ($e->getMessage() !== '')
    {
        $dettaglio .= ' ' . $e->getMessage();
    }
    $messaggioStato = VisualizzaStore::erroreSchemaMessaggio($dettaglio);
    $totRighe = 0;
    $totPagine = 1;
    $righe = array();
    $mostraForm = false;
    $rigaInModifica = null;
}

// Vista lista/form condivisa 5 tab
require __DIR__ . '/visualizza_view.php';
