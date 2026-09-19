<?php
// reserved/visualizza.php — lista unificata 5 tab in linguaggio cassa.
// Layout: switch tab in nav verticale dentro aside, lista in main 5 righe/pagina

// Avvia la sessione e blocca l'accesso diretto senza login riservato
session_start();
if (empty($_SESSION['reserved_auth']))
{
    header('Location: login.php?msg=2');
    exit;
}

//carica connessione mysqli e funzioni condivise
require_once __DIR__ . '/../dbConnect.php';

if (!defined('DEBUG'))
{
    define('DEBUG', 0);
}
require_once __DIR__ . '/../funzioni.inc';

//Gestisce logout e riporta al form di login
if (isset($_GET['logout']))
{
    unset($_SESSION['reserved_auth']);
    session_regenerate_id(true);
    header('Location: login.php');
    exit;
}

// niente SQL da $_GET non validato: whitelist tab/OR/DIR + pagina int
// Configura 5 tab: from/select per la query, orders ammessi, default sort, colonne di ricerca e intestazioni
//$righePerPagina -> righe per pagina della lista
$righePerPagina = 5;
$configTabs = array(
    'categorie' => array('label' => 'CATEGORIE', 'titolo' => 'GESTIONE CATEGORIE',
        'from' => '`categorie`',
        'select' => '`id_categoria`, `descrizione_cat`, `testo_bottone`, `colore`',
        'cnt' => 'COUNT(*)',
        'orders' => array('descrizione_cat' => '`descrizione_cat`', 'testo_bottone' => '`testo_bottone`', 'colore' => '`colore`'),
        'default' => '`descrizione_cat` ASC',
        'search' => array('`descrizione_cat`', '`testo_bottone`'),
        'headers' => array(array('descrizione_cat', 'CATEGORIA'), array('testo_bottone', 'BOTTONE'), array('colore', 'COLORE'))),
    'prodotti' => array('label' => 'PRODOTTI', 'titolo' => 'GESTIONE PRODOTTI',
        'from' => '`prodotti`',
        'select' => '`id_prodotto`, `descrizione_prod`, `prezzo`, `olpp`, `barcode`, `testo_biglietto`',
        'cnt' => 'COUNT(*)',
        'orders' => array('descrizione_prod' => '`descrizione_prod`', 'prezzo' => '`prezzo`', 'olpp' => '`olpp`', 'barcode' => '`barcode`'),
        'default' => '`descrizione_prod` ASC',
        'search' => array('`descrizione_prod`', '`testo_biglietto`', '`barcode`'),
        'headers' => array(array('descrizione_prod', 'DESCRIZIONE'), array('prezzo', 'PREZZO'), array('olpp', 'ETICH'))),
    'prodotti_categorie' => array('label' => 'POSIZIONI', 'titolo' => 'GESTIONE POSIZIONI',
        'from' => '`prodotti_categorie` `pc` LEFT JOIN `prodotti` `p` ON `p`.`id_prodotto` = `pc`.`id_prodotto` LEFT JOIN `categorie` `c` ON `c`.`id_categoria` = `pc`.`id_categoria`',
        'select' => '`pc`.`id_prodotto`, `pc`.`id_categoria`, `pc`.`posizione`, COALESCE(`p`.`descrizione_prod`, \'(ORFANO)\') AS `prodotto`, COALESCE(`c`.`descrizione_cat`, \'(ORFANA)\') AS `categoria`',
        'cnt' => 'COUNT(*)',
        'orders' => array('prodotto' => '`prodotto`', 'categoria' => '`categoria`', 'posizione' => '`pc`.`posizione`'),
        'default' => '`pc`.`posizione` ASC',
        'search' => array('`p`.`descrizione_prod`', '`c`.`descrizione_cat`'),
        'headers' => array(array('prodotto', 'PRODOTTO'), array('categoria', 'CATEGORIA'), array('posizione', 'POS'))),
    'contatori' => array('label' => 'PRODOTTI VENDUTI', 'titolo' => 'GESTIONE PRODOTTI VENDUTI',
        'from' => '`contatori`',
        'select' => '`id_contatore`, `nome`, `limite_qta`, `controllo_periodo`, `data_da`, `data_a`, `attivo`, `attivo_app`',
        'cnt' => 'COUNT(*)',
        'orders' => array('nome' => '`nome`', 'limite_qta' => '`limite_qta`', 'attivo' => '`attivo`'),
        'default' => '`nome` ASC',
        'search' => array('`nome`'),
        'headers' => array(array('nome', 'NOME'), array('limite_qta', 'LIMITE'), array('attivo', 'ATTIVO'))),
    'prodotti_contatori' => array('label' => 'REGOLE VENDUTI', 'titolo' => 'GESTIONE REGOLE VENDUTI',
        'from' => '`prodotti_contatori` `pc` LEFT JOIN `contatori` `c` ON `c`.`id_contatore` = `pc`.`id_contatore` LEFT JOIN `prodotti` `p` ON `p`.`id_prodotto` = `pc`.`id_prodotto`',
        'select' => '`pc`.`id_contatore`, `pc`.`id_prodotto`, `pc`.`quantita`, COALESCE(`c`.`nome`, \'(ORFANO)\') AS `contatore`, COALESCE(`p`.`descrizione_prod`, \'(ORFANO)\') AS `prodotto`',
        'cnt' => 'COUNT(*)',
        'orders' => array('contatore' => '`contatore`', 'prodotto' => '`prodotto`', 'quantita' => '`pc`.`quantita`'),
        'default' => '`contatore` ASC',
        'search' => array('`c`.`nome`', '`p`.`descrizione_prod`'),
        'headers' => array(array('contatore', 'VENDUTO'), array('prodotto', 'PRODOTTO'), array('quantita', 'QTA'))),
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
    if ($colonnaOrd !== '')
    {
        $orderBySql = $tabConfig['orders'][$colonnaOrd] . ' ' . $direzioneOrd;
    }
    else
    {
        $orderBySql = $tabConfig['default'];
    }
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

// Costruisce un link a visualizza.php con i parametri dati, già url-encoded
function urlLista($params)
{
    return 'visualizza.php?' . http_build_query($params);
}

// Dizionario colori 01..14 (nomi definitivi Chiara in issue, hex in functionsFrontend.inc::coloreCategoriaHex):
// unico punto di verita' per normalizzazione + rendering. Codici stabili, DB invariato.
// Salvataggio = numero zero-padded; lista = NOME(XX); ignoti in lista = raw.
function mappaColori()
{
    static $mappa = array(
        'ROSSO' => '01', 'ARANCIONE' => '02', 'OCRA' => '03', 'GIALLO' => '04',
        'VERDE' => '05', 'VERDE SCURO' => '06', 'AZZURRO' => '07', 'BLU' => '08',
        'VIOLA' => '09', 'LILLA' => '10', 'VIOLA CHIARO' => '11', 'ROSA SCURO' => '12',
        'ROSA' => '13', 'ROSA CHIARO' => '14',
    );
    return $mappa;
}

// Normalizza input COLORE (nome case-ins. o numero con/senza zero-pad) a numero zero-padded.
// Rifiuta (false): forma con parentesi tipo GIALLO(03), nomi ignoti, numeri fuori 01..14.
function normalizzaColore($raw)
{
    $colore = strtoupper(trim($raw));
    if ($colore === '' || strpos($colore, '(') !== false || strpos($colore, ')') !== false)
    {
        return false;
    }
    $mappa = mappaColori();
    if (isset($mappa[$colore]))
    {
        return $mappa[$colore];
    }
    // Nomi con spazi (es. VIOLA CHIARO) accettati anche senza spazi.
    $compatto = str_replace(' ', '', $colore);
    foreach ($mappa as $nome => $codice)
    {
        if (str_replace(' ', '', $nome) === $compatto)
        {
            return $codice;
        }
    }
    if (ctype_digit($colore))
    {
        $num = (int)$colore;
        if ($num >= 1 && $num <= 14)
        {
            return sprintf('%02d', $num);
        }
    }
    return false;
}

// Etichetta lista COLORE: NOME(XX) da dizionario, raw se codice ignoto.
function etichettaColore($codice)
{
    $codice = strtoupper(trim((string)$codice));
    if (ctype_digit($codice))
    {
        $codice = sprintf('%02d', (int)$codice);
    }
    $nome = array_search($codice, mappaColori(), true);
    if ($nome !== false)
    {
        return $nome . '(' . $codice . ')';
    }
    return $codice;
}

// Converte il flag T/F del DB in etichetta SI/NO per la lista
function etichettaSiNo($flag)
{
    if ($flag === 'T')
    {
        return 'SI';
    }
    else
    {
        return 'NO';
    }
}

// Modalita' fiera (issue): legge set.inc senza includerlo (set.inc ha side-effect fopen),
// default OFF se assente. "1" = barcode visibile/obbligatorio, "0" = nascosto con default '-'.
function fieraAttiva()
{
    $contenuto = @file_get_contents(__DIR__ . '/../set.inc');
    return $contenuto !== false && strpos($contenuto, 'define("MODALITA_FIERA", "1")') !== false;
}

//Azioni riga protette 5 tab: nuovo/modifica/elimina con prepared.
// Blocco relation applicativo come vecchio visualizza.php (MyISAM = zero FK); al posto dell'alert legacy resta in lista con messaggio. Sole classi style.css.
// Messaggio di stato mostrato in lista o nel form
$messaggioStato = '';

// Converte un errore DB grezzo in messaggio leggibile da cassa: dice quale
// colonna/tabella manca invece di lasciare schermo bianco (display_errors=0).
function erroreSchemaMessaggio($dettaglio)
{
    $dettaglio = (string)$dettaglio;
    if (preg_match("/Unknown column '([^']+)'/i", $dettaglio, $m))
    {
        return "COLONNA '" . $m[1] . "' MANCANTE — CONTATTA ASSISTENZA";
    }
    if (preg_match("/Table '([^']+)' doesn't exist/i", $dettaglio, $m))
    {
        $tabella = $m[1];
        $pos = strrpos($tabella, '.');
        if ($pos !== false)
        {
            $tabella = substr($tabella, $pos + 1);
        }
        return "TABELLA '" . $tabella . "' MANCANTE — CONTATTA ASSISTENZA";
    }
    return 'ERRORE DB — CONTATTA ASSISTENZA';
}

// Conta righe con prepared statement per controlli referenziali e duplicati
function contaRighe($mysqli, $sql, $tipiBind, $valoriBind)
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt)
    {
        throw new RuntimeException($mysqli->error !== '' ? $mysqli->error : 'prepare fallita');
    }
    if ($tipiBind !== '')
    {
        $stmt->bind_param($tipiBind, ...$valoriBind);
    }

    $stmt->execute();
    $conteggio = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $conteggio;
}

// Ogni query sotto puo fallire per schema drift (colonna/tabella mancante):
// il catch prima dell'HTML trasforma il fatal in messaggio leggibile e la
// pagina si apre comunque, degrada solo il tab coinvolto.
try
{

// Elimina prodotto solo se non è citato in posizioni, regole venduti e righe ordini
// Solo POST con token, GET resta lettura: link ?del= senza token rifiutato.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['del']) && (!$tabValido || $tabCorrente === 'prodotti'))
{
    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
    $idProdotto = (int)$_POST['del'];
    $numRiferimenti = contaRighe($mysqli, "SELECT COUNT(*) AS c FROM `prodotti_categorie` WHERE id_prodotto = ?", 'i', array($idProdotto))
        + contaRighe($mysqli, "SELECT COUNT(*) AS c FROM `prodotti_contatori` WHERE id_prodotto = ?", 'i', array($idProdotto))
        + contaRighe($mysqli, "SELECT COUNT(*) AS c FROM `righe_ordini` WHERE id_prodotto = ?", 'i', array($idProdotto));

    if ($numRiferimenti > 0)
    {
        $messaggioStato = 'PRODOTTO REFERENZIATO — ELIMINA BLOCCATA';
    }    else
    {
        $stmt = $mysqli->prepare("DELETE FROM `prodotti` WHERE id_prodotto = ?");
        $stmt->bind_param('i', $idProdotto);
        $stmt->execute();
        header('Location: visualizza.php?tab=prodotti');
        exit;
    }
    }
}
elseif (isset($_GET['del']) && (!$tabValido || $tabCorrente === 'prodotti'))
{
    $messaggioStato = 'AZIONE NON VALIDA';
}
// Elimina categoria solo se nessuna posizione la usa ancora
if ($tabValido && $tabCorrente === 'categorie' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['del']))
{
    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
    $idCategoria = (int)$_POST['del'];
    if (contaRighe($mysqli, "SELECT COUNT(*) AS c FROM `prodotti_categorie` WHERE id_categoria = ?", 'i', array($idCategoria)) > 0)
    {
        $messaggioStato = 'CATEGORIA REFERENZIATA — ELIMINA BLOCCATA';
    }
    else
    {
        $stmt = $mysqli->prepare("DELETE FROM `categorie` WHERE id_categoria = ?");
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();
        header('Location: visualizza.php?tab=categorie');
        exit;
    }
    }
}
elseif ($tabValido && $tabCorrente === 'categorie' && isset($_GET['del']))
{
    $messaggioStato = 'AZIONE NON VALIDA';
}
// Elimina venduto a cascata con le sue regole (MyISAM = zero FK, cascade applicativa)
if ($tabValido && $tabCorrente === 'contatori' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['del']))
{
    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
    $idContatore = (int)$_POST['del'];
    $stmt = $mysqli->prepare("DELETE FROM `prodotti_contatori` WHERE id_contatore = ?");
    $stmt->bind_param('i', $idContatore);
    $stmt->execute();
    $stmt->close();
    $stmt = $mysqli->prepare("DELETE FROM `contatori` WHERE id_contatore = ?");
    $stmt->bind_param('i', $idContatore);
    $stmt->execute();
    header('Location: visualizza.php?tab=contatori');
    exit;
    }
}
elseif ($tabValido && $tabCorrente === 'contatori' && isset($_GET['del']))
{
    $messaggioStato = 'AZIONE NON VALIDA';
}
// Elimina posizione puntuale identificata dalla chiave tripla prodotto+categoria+posizione
if ($tabValido && $tabCorrente === 'prodotti_categorie' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['del_p'], $_POST['del_c'], $_POST['del_pos']))
{
    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
    $idProdotto = (int)$_POST['del_p'];
    $idCategoria = (int)$_POST['del_c'];
    $posizione = (int)$_POST['del_pos'];
    $stmt = $mysqli->prepare("DELETE FROM `prodotti_categorie` WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?");
    $stmt->bind_param('iii', $idProdotto, $idCategoria, $posizione);
    $stmt->execute();
    header('Location: visualizza.php?tab=prodotti_categorie');
    exit;
    }
}
elseif ($tabValido && $tabCorrente === 'prodotti_categorie' && isset($_GET['del_p'], $_GET['del_c'], $_GET['del_pos']))
{
    $messaggioStato = 'AZIONE NON VALIDA';
}
//elimina regola venduti identificata dalla coppia venduto+prodotto
if ($tabValido && $tabCorrente === 'prodotti_contatori' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['del_c'], $_POST['del_p']))
{
    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
    $idContatore = (int)$_POST['del_c'];
    $idProdotto = (int)$_POST['del_p'];
    $stmt = $mysqli->prepare("DELETE FROM `prodotti_contatori` WHERE id_contatore = ? AND id_prodotto = ?");
    $stmt->bind_param('ii', $idContatore, $idProdotto);
    $stmt->execute();
    header('Location: visualizza.php?tab=prodotti_contatori');
    exit;
    }
}
elseif ($tabValido && $tabCorrente === 'prodotti_contatori' && isset($_GET['del_c'], $_GET['del_p']))
{
    $messaggioStato = 'AZIONE NON VALIDA';
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
    $idProdotto = (int)$_GET['edit'];
    $stmt = $mysqli->prepare("SELECT id_prodotto, descrizione_prod, prezzo, iva, testo_biglietto, olpp, barcode FROM `prodotti` WHERE id_prodotto = ?");
    $stmt->bind_param('i', $idProdotto);
    $stmt->execute();
    $rigaInModifica = $stmt->get_result()->fetch_assoc();
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
//Carica la categoria da modificare, torna in lista se l'id non esiste
if ($tabForm === 'categorie' && isset($_GET['edit']))
{
    $idCategoria = (int)$_GET['edit'];
    $stmt = $mysqli->prepare("SELECT id_categoria, descrizione_cat, testo_bottone, colore FROM `categorie` WHERE id_categoria = ?");
    $stmt->bind_param('i', $idCategoria);
    $stmt->execute();
    $rigaInModifica = $stmt->get_result()->fetch_assoc();
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
// Carica il venduto da modificare, torna in lista se l'id non esiste
if ($tabForm === 'contatori' && isset($_GET['edit']))
{
    $idContatore = (int)$_GET['edit'];
    $stmt = $mysqli->prepare("SELECT id_contatore, nome, limite_qta, controllo_periodo, data_da, data_a, attivo, attivo_app FROM `contatori` WHERE id_contatore = ?");
    $stmt->bind_param('i', $idContatore);
    $stmt->execute();
    $rigaInModifica = $stmt->get_result()->fetch_assoc();
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
// Carica la posizione da modificare tramite chiave tripla, torna in lista se assente
if ($tabForm === 'prodotti_categorie' && isset($_GET['edit_p'], $_GET['edit_c'], $_GET['edit_pos']))
{
    $idProdotto = (int)$_GET['edit_p'];
    $idCategoria = (int)$_GET['edit_c'];
    $posizione = (int)$_GET['edit_pos'];
    $stmt = $mysqli->prepare("SELECT id_prodotto, id_categoria, posizione FROM `prodotti_categorie` WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?");
    $stmt->bind_param('iii', $idProdotto, $idCategoria, $posizione);
    $stmt->execute();
    $rigaInModifica = $stmt->get_result()->fetch_assoc();
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}
// Carica la regola venduti da modificare tramite coppia venduto+prodotto
if ($tabForm === 'prodotti_contatori' && isset($_GET['edit_c'], $_GET['edit_p']))
{
    $idContatore = (int)$_GET['edit_c'];
    $idProdotto = (int)$_GET['edit_p'];
    $stmt = $mysqli->prepare("SELECT id_contatore, id_prodotto, quantita FROM `prodotti_contatori` WHERE id_contatore = ? AND id_prodotto = ?");
    $stmt->bind_param('ii', $idContatore, $idProdotto);
    $stmt->execute();
    $rigaInModifica = $stmt->get_result()->fetch_assoc();
    if (!$rigaInModifica)
    {
        $mostraForm = false;
    }
}

// Salva il form: valida il tab inviato, ripulisce i campi e fa update o insert con prepared
if (isset($_POST['save']))
{
    $tabInviato = (isset($_POST['t']) && isset($configTabs[$_POST['t']])) ? $_POST['t'] : 'prodotti';

    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    // Salva prodotto: descrizione obbligatoria, prezzo double-only, iva fissa 0.22 (input rimosso, colonna NOT NULL),
    // barcode obbligatorio solo a fiera attiva, default '-' a fiera spenta (issue). Nuovo id come max+1.
    elseif ($tabInviato === 'prodotti')
    {
        $idProdotto = (int)$_POST['id'];
        $descrizione = substr(trim($_POST['descrizione_prod']), 0, 100);
        $prezzoRaw = trim($_POST['prezzo'] ?? '');
        $iva = 0.22;
        $testoBiglietto = substr(trim($_POST['testo_biglietto']), 0, 100);
        $olpp = $_POST['olpp'] == 'T' ? 'T' : 'F';
        $fiera = fieraAttiva();
        if (!$fiera && $idProdotto === 0)
        {
            $barcode = '-';
        }
        else
        {
            $barcode = substr(trim($_POST['barcode'] ?? ''), 0, 20);
        }

        if ($descrizione === '' || $barcode === '')
        {
            $messaggioStato = 'DESCRIZIONE E BARCODE OBBLIGATORI';
        }
        elseif (!is_numeric($prezzoRaw) || (float)$prezzoRaw < 0)
        {
            $messaggioStato = 'PREZZO NON VALIDO';
        }
        else
        {
            $prezzo = (float)$prezzoRaw;
            if ($idProdotto > 0)
            {
                $stmt = $mysqli->prepare("UPDATE `prodotti` SET descrizione_prod = ?, prezzo = ?, iva = ?, testo_biglietto = ?, olpp = ?, barcode = ? WHERE id_prodotto = ?");
                $stmt->bind_param('sddsssi', $descrizione, $prezzo, $iva, $testoBiglietto, $olpp, $barcode, $idProdotto);
                $stmt->execute();
            }
            else
            {
                // id AUTO_INCREMENT, niente MAX+1 (race su MyISAM senza lock).
                $stmt = $mysqli->prepare("INSERT INTO `prodotti` (descrizione_prod, prezzo, iva, testo_biglietto, olpp, barcode) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sddsss', $descrizione, $prezzo, $iva, $testoBiglietto, $olpp, $barcode);
                $stmt->execute();
            }
            header('Location: visualizza.php?tab=prodotti');
            exit;
        }
        $tabCorrente = 'prodotti';
        $tabValido = true;
        $tabConfig = $configTabs['prodotti'];
        $tabForm = 'prodotti';
        $mostraForm = true;
    }

    // Salva categoria: descrizione, bottone e colore obbligatori
    elseif ($tabInviato === 'categorie')
    {
        $idCategoria = (int)$_POST['id'];
        $descrizione = substr(trim($_POST['descrizione_cat']), 0, 30);
        $testoBottone = substr(trim($_POST['testo_bottone']), 0, 20);
        $coloreInput = substr(trim($_POST['colore']), 0, 20);

        if ($descrizione === '' || $testoBottone === '' || $coloreInput === '')
        {
            $messaggioStato = 'DESCRIZIONE, BOTTONE E COLORE OBBLIGATORI';
            $colore = $coloreInput;
        }
        else
        {
            $colore = normalizzaColore($coloreInput);
            if ($colore === false)
            {
                $messaggioStato = 'COLORE NON VALIDO - USA NOME O NUMERO';
                $colore = $coloreInput;
            }
        }

        if ($messaggioStato === '')
        {
            if ($idCategoria > 0)
            {
                $stmt = $mysqli->prepare("UPDATE `categorie` SET descrizione_cat = ?, testo_bottone = ?, colore = ? WHERE id_categoria = ?");
                $stmt->bind_param('sssi', $descrizione, $testoBottone, $colore, $idCategoria);
                $stmt->execute();
            }
            else
            {
                $stmt = $mysqli->prepare("INSERT INTO `categorie` (descrizione_cat, testo_bottone, colore) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $descrizione, $testoBottone, $colore);
                $stmt->execute();
            }
            header('Location: visualizza.php?tab=categorie');
            exit;
        }

        if ($messaggioStato === '')
        {
            $messaggioStato = 'DESCRIZIONE, BOTTONE E COLORE OBBLIGATORI';
        }
        $tabCorrente = 'categorie';
        $tabValido = true;
        $tabConfig = $configTabs['categorie'];
        $tabForm = 'categorie';
        $mostraForm = true;
        $rigaInModifica = array('id_categoria' => $idCategoria, 'descrizione_cat' => $descrizione, 'testo_bottone' => $testoBottone, 'colore' => $colore);
    }

    // Salva venduto: nome e date obbligatori, flag T/F e date normalizzate in formato DB
    elseif ($tabInviato === 'contatori')
    {
        $idContatore = (int)$_POST['id'];
        $nome = substr(trim($_POST['nome']), 0, 50);
        $limiteQta = max(0, (int)$_POST['limite_qta']);
        $flagPeriodo = (isset($_POST['controllo_periodo']) && $_POST['controllo_periodo'] == 'T') ? 'T' : 'F';
        $tsDa = strtotime($_POST['data_da']);
        $tsA = strtotime($_POST['data_a']);
        $flagAttivo = (isset($_POST['attivo']) && $_POST['attivo'] == 'T') ? 'T' : 'F';
        $flagApp = (isset($_POST['attivo_app']) && $_POST['attivo_app'] == 'T') ? 'T' : 'F';
        if ($nome !== '' && $tsDa !== false && $tsA !== false)
        {
            $dataDaDb = date('Y-m-d H:i:s', $tsDa);
            $dataADb = date('Y-m-d H:i:s', $tsA);

            if ($idContatore > 0)
            {
                $stmt = $mysqli->prepare("UPDATE `contatori` SET nome = ?, limite_qta = ?, controllo_periodo = ?, data_da = ?, data_a = ?, attivo = ?, attivo_app = ? WHERE id_contatore = ?");
                $stmt->bind_param('sisssssi', $nome, $limiteQta, $flagPeriodo, $dataDaDb, $dataADb, $flagAttivo, $flagApp, $idContatore);
                $stmt->execute();
            }
            else
            {
                $stmt = $mysqli->prepare("INSERT INTO `contatori` (nome, limite_qta, controllo_periodo, data_da, data_a, attivo, attivo_app) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sisssss', $nome, $limiteQta, $flagPeriodo, $dataDaDb, $dataADb, $flagAttivo, $flagApp);
                $stmt->execute();
            }
            header('Location: visualizza.php?tab=contatori');
            exit;
        }
        $messaggioStato = 'NOME E DATE OBBLIGATORI';
        $tabCorrente = 'contatori';
        $tabValido = true;
        $tabConfig = $configTabs['contatori'];
        $tabForm = 'contatori';
        $mostraForm = true;
        $rigaInModifica = array('id_contatore' => $idContatore, 'nome' => $nome, 'limite_qta' => $limiteQta, 'controllo_periodo' => $flagPeriodo, 'data_da' => $_POST['data_da'], 'data_a' => $_POST['data_a'], 'attivo' => $flagAttivo, 'attivo_app' => $flagApp);
    }

    //salva posizione: griglia 01-24 unica via (issue), dup sulla tripla ancora bloccata
    elseif ($tabInviato === 'prodotti_categorie')
    {
        $idProdotto = (int)$_POST['id_prodotto'];
        $idCategoria = (int)$_POST['id_categoria'];
        $posizione = (int)$_POST['posizione'];
        $vecchioIdProdotto = (int)$_POST['oid_p'];
        $vecchioIdCategoria = (int)$_POST['oid_c'];
        $vecchiaPosizione = (int)$_POST['oid_pos'];
        $inModifica = isset($_POST['is_edit']) && $_POST['is_edit'] === '1';
        $rigaInModifica = array('id_prodotto' => $idProdotto, 'id_categoria' => $idCategoria, 'posizione' => $posizione);

        if ($idProdotto <= 0 || $idCategoria <= 0)
        {
            $messaggioStato = 'PRODOTTO E CATEGORIA OBBLIGATORI';
        }
        elseif ($posizione < 1 || $posizione > 24)
        {
            $messaggioStato = 'POSIZIONE 01-24 OBBLIGATORIA';
        }
        else
        {
            $numDuplicati = contaRighe($mysqli, "SELECT COUNT(*) AS c FROM `prodotti_categorie` WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?", 'iii', array($idProdotto, $idCategoria, $posizione));
            if ($inModifica && $idProdotto == $vecchioIdProdotto && $idCategoria == $vecchioIdCategoria && $posizione == $vecchiaPosizione)
            {
                $numDuplicati = 0;
            }

            if ($numDuplicati > 0)
            {
                $messaggioStato = 'POSIZIONE GIA ESISTENTE';
            }
            else
            {
                if ($inModifica)
                {
                    $stmt = $mysqli->prepare("UPDATE `prodotti_categorie` SET id_prodotto = ?, id_categoria = ?, posizione = ? WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?");
                    $stmt->bind_param('iiiiii', $idProdotto, $idCategoria, $posizione, $vecchioIdProdotto, $vecchioIdCategoria, $vecchiaPosizione);
                    $stmt->execute();
                }
                else
                {
                    $stmt = $mysqli->prepare("INSERT INTO `prodotti_categorie` (id_prodotto, id_categoria, posizione) VALUES (?, ?, ?)");
                    $stmt->bind_param('iii', $idProdotto, $idCategoria, $posizione);
                    $stmt->execute();
                }
                header('Location: visualizza.php?tab=prodotti_categorie');
                exit;
            }
        }
        if ($messaggioStato === '')
        {
            $messaggioStato = 'PRODOTTO E CATEGORIA OBBLIGATORI';
        }

        $tabCorrente = 'prodotti_categorie';
        $tabValido = true;
        $tabConfig = $configTabs['prodotti_categorie'];
        $tabForm = 'prodotti_categorie';
        $mostraForm = true;
    }

    //Salva regola venduti: blocca i duplicati sulla coppia venduto+prodotto
    elseif ($tabInviato === 'prodotti_contatori')
    {
        $idContatore = (int)$_POST['id_contatore'];
        $idProdotto = (int)$_POST['id_prodotto'];
        $quantita = max(0, (int)$_POST['quantita']);
        $vecchioIdContatore = (int)$_POST['oid_c'];
        $vecchioIdProdotto = (int)$_POST['oid_p'];
        $inModifica = isset($_POST['is_edit']) && $_POST['is_edit'] === '1';
        $rigaInModifica = array('id_contatore' => $idContatore, 'id_prodotto' => $idProdotto, 'quantita' => $quantita);

        if ($idContatore > 0 && $idProdotto > 0)
        {
            $numDuplicati = contaRighe($mysqli, "SELECT COUNT(*) AS c FROM `prodotti_contatori` WHERE id_contatore = ? AND id_prodotto = ?", 'ii', array($idContatore, $idProdotto));
            if ($inModifica && $idContatore == $vecchioIdContatore && $idProdotto == $vecchioIdProdotto)
            {
                $numDuplicati = 0;
            }

            if ($numDuplicati > 0)
            {
                $messaggioStato = 'REGOLA GIA ESISTENTE';
            }
            else
            {
                if ($inModifica)
                {
                    $stmt = $mysqli->prepare("UPDATE `prodotti_contatori` SET id_contatore = ?, id_prodotto = ?, quantita = ? WHERE id_contatore = ? AND id_prodotto = ?");
                    $stmt->bind_param('iiiii', $idContatore, $idProdotto, $quantita, $vecchioIdContatore, $vecchioIdProdotto);
                    $stmt->execute();
                }
                else
                {
                    $stmt = $mysqli->prepare("INSERT INTO `prodotti_contatori` (id_contatore, id_prodotto, quantita) VALUES (?, ?, ?)");
                    $stmt->bind_param('iii', $idContatore, $idProdotto, $quantita);
                    $stmt->execute();
                }
                header('Location: visualizza.php?tab=prodotti_contatori');
                exit;
            }
        }
        else
        {
            $messaggioStato = 'VENDUTO E PRODOTTO OBBLIGATORI';
        }

        if ($messaggioStato === '')
        {
            $messaggioStato = 'VENDUTO E PRODOTTO OBBLIGATORI';
        }

        $tabCorrente = 'prodotti_contatori';
        $tabValido = true;
        $tabConfig = $configTabs['prodotti_contatori'];
        $tabForm = 'prodotti_contatori';
        $mostraForm = true;
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
    $res = mysql_query_safe($mysqli, "SELECT id_prodotto, descrizione_prod FROM `prodotti` ORDER BY descrizione_prod");
    while ($opzione = $res->fetch_assoc())
    {
        $opzioniProdotti[] = $opzione;
    }
}

//Option categorie + occupancy griglia 01-24 solo per il form posizioni
if ($mostraForm && $tabForm === 'prodotti_categorie')
{
    $res = mysql_query_safe($mysqli, "SELECT id_categoria, descrizione_cat FROM `categorie` ORDER BY descrizione_cat");
    while ($opzione = $res->fetch_assoc())
    {
        $opzioniCategorie[] = $opzione;
    }
    $resOcc = mysql_query_safe($mysqli, "SELECT id_categoria, posizione FROM `prodotti_categorie`");
    while ($occ = $resOcc->fetch_assoc())
    {
        $mappaPosizioni[(int)$occ['id_categoria']][] = (int)$occ['posizione'];
    }
}
// Option venduti solo per il form regole
if ($mostraForm && $tabForm === 'prodotti_contatori')
{
    $res = mysql_query_safe($mysqli, "SELECT id_contatore, nome FROM `contatori` ORDER BY nome");
    while ($opzione = $res->fetch_assoc())
    {
        $opzioniContatori[] = $opzione;
    }
}

// Lista: COUNT(*) + LIMIT offset,rpp — mai offset esposto in URL
// Conta il totale con la stessa WHERE di ricerca, poi fissa pagina e offset dentro i limiti
$totRighe = 0;
$totPagine = 1;
$righe = array();

if ($tabValido)
{
    // Costruisce la WHERE di ricerca con LIKE su tutte le colonne del tab
    $where = '';
    $tipi = '';
    $vals = array();
    if ($ricerca !== '')
    {
        $w = array();
        foreach ($tabConfig['search'] as $colRicerca)
        {
            $w[] = $colRicerca . ' LIKE ?';
        }

        $where = ' WHERE (' . implode(' OR ', $w) . ')';

        foreach ($tabConfig['search'] as $colRicerca)
        {
            $tipi .= 's';
            $vals[] = '%' . $ricerca . '%';
        }
    }
    $stmt = $mysqli->prepare("SELECT " . $tabConfig['cnt'] . " AS c FROM " . $tabConfig['from'] . $where);

    if ($tipi !== '')
    {
        $stmt->bind_param($tipi, ...$vals);
    }
    $stmt->execute();
    $totRighe = (int)$stmt->get_result()->fetch_assoc()['c'];
    $totPagine = max(1, (int)ceil($totRighe / $righePerPagina));

    if ($paginaCorrente > $totPagine)
    {
        $paginaCorrente = $totPagine;
    }

    $offset = ($paginaCorrente - 1) * $righePerPagina;

    // Legge solo le 5 righe della pagina corrente con lo stesso filtro della COUNT
    $stmt = $mysqli->prepare("SELECT " . $tabConfig['select'] . " FROM " . $tabConfig['from'] . $where . " ORDER BY " . $orderBySql . " LIMIT " . (int)$offset . ", " . (int)$righePerPagina);

    if ($tipi !== '')
    {
        $stmt->bind_param($tipi, ...$vals);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    while ($riga = $res->fetch_assoc())
    {
        $righe[] = $riga;
    }
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
    $messaggioStato = erroreSchemaMessaggio($dettaglio);
    $totRighe = 0;
    $totPagine = 1;
    $righe = array();
    $mostraForm = false;
    $rigaInModifica = null;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tabValido ? htmlspecialchars($tabConfig['titolo']) : 'TABELLA NON TROVATA'; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<!-- Vista form quando si crea o modifica, vista lista con ricerca e paginazione altrimenti -->
<?php if ($mostraForm): ?>
    <?php
    // Etichette titolo e flag edit: nuovo forza inserimento anche se $rigaInModifica è valorizzato
    $titoliForm = array('categorie' => 'CATEGORIA', 'prodotti' => 'PRODOTTO', 'prodotti_categorie' => 'POSIZIONE', 'contatori' => 'VENDUTO', 'prodotti_contatori' => 'REGOLA');
    $inModifica = $rigaInModifica && !isset($_GET['nuovo']);
    // Action del form: conserva la chiave originale così il POST aggiorna la riga giusta
    if ($tabForm === 'prodotti_categorie' && $inModifica)
        $actionForm = 'visualizza.php?tab=prodotti_categorie&edit_p=' . (int)$rigaInModifica['id_prodotto'] . '&edit_c=' . (int)$rigaInModifica['id_categoria'] . '&edit_pos=' . (int)$rigaInModifica['posizione'];
    elseif ($tabForm === 'prodotti_contatori' && $inModifica)
        $actionForm = 'visualizza.php?tab=prodotti_contatori&edit_c=' . (int)$rigaInModifica['id_contatore'] . '&edit_p=' . (int)$rigaInModifica['id_prodotto'];
    elseif ($inModifica && in_array($tabForm, array('categorie', 'prodotti', 'contatori')))
        $actionForm = 'visualizza.php?tab=' . $tabForm . '&edit=' . (int)reset($rigaInModifica);
    else
        $actionForm = 'visualizza.php?tab=' . $tabForm . '&nuovo=1';
    ?>
    <main style="width:100%;align-items:center;text-align:center;">
        <header style="justify-content:center;"><h1><?php echo $inModifica ? 'MODIFICA ' . $titoliForm[$tabForm] . ' #' . (int)reset($rigaInModifica) : 'NUOVO ' . $titoliForm[$tabForm]; ?></h1></header>
        <section class="admin-panel" style="align-items:center;max-width:560px;">
            <?php if ($messaggioStato != ''): ?>
                <h3 class="admin-group-title"><?php echo htmlspecialchars($messaggioStato); ?></h3>
            <?php endif; ?>
            <!-- Form categoria: descrizione, testo bottone e colore -->
            <?php if ($tabForm === 'categorie'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-categoria" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="categorie">
                <input type="hidden" name="id" value="<?php echo $inModifica ? (int)$rigaInModifica['id_categoria'] : 0; ?>">
                <?php csrf_field(); ?>
                <input type="text" name="descrizione_cat" class="codice-text-field" maxlength="30" required placeholder="DESCRIZIONE" value="<?php echo htmlspecialchars($rigaInModifica['descrizione_cat'] ?? ''); ?>">
                <input type="text" name="testo_bottone" class="codice-text-field" maxlength="20" required placeholder="TESTO BOTTONE" value="<?php echo htmlspecialchars($rigaInModifica['testo_bottone'] ?? ''); ?>">
                <input type="text" name="colore" class="codice-text-field" maxlength="12" required placeholder="COLORE (01..14)" value="<?php echo htmlspecialchars($rigaInModifica['colore'] ?? ''); ?>">
                <!-- Tastiera touch (variante A da issue): details espandibile, tasti 48px, CANC slice, focus ultimo campo -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kb-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kb-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-kb-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=categorie" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-campi">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form venduto: nome, limite, flag periodo/attivo/app e intervallo date -->
            <?php elseif ($tabForm === 'contatori'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-contatore" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="contatori">
                <input type="hidden" name="id" value="<?php echo $inModifica ? (int)$rigaInModifica['id_contatore'] : 0; ?>">
                <?php csrf_field(); ?>
                <input type="text" name="nome" class="codice-text-field" maxlength="50" required placeholder="NOME" value="<?php echo htmlspecialchars($rigaInModifica['nome'] ?? ''); ?>">
                <input type="number" name="limite_qta" class="codice-text-field" step="1" min="0" placeholder="LIMITE QTA" value="<?php echo htmlspecialchars($rigaInModifica['limite_qta'] ?? ''); ?>">
                <div class="admin-btn-row" style="width:100%;">
                    <div style="flex:1;display:flex;flex-direction:column;">
                        <span class="admin-group-title">CONTROLLO PERIODO</span>
                    <select name="controllo_periodo" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['controllo_periodo'] ?? 'F') == 'T' ? ' selected' : ''; ?>>PERIODO SI</option>
                        <option value="F"<?php echo ($rigaInModifica['controllo_periodo'] ?? 'F') == 'F' ? ' selected' : ''; ?>>PERIODO NO</option>
                    </select>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;">
                        <span class="admin-group-title">ATTIVO</span>
                    <select name="attivo" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['attivo'] ?? 'T') == 'T' ? ' selected' : ''; ?>>ATTIVO SI</option>
                        <option value="F"<?php echo ($rigaInModifica['attivo'] ?? '') == 'F' ? ' selected' : ''; ?>>ATTIVO NO</option>
                    </select>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;">
                        <span class="admin-group-title">ATTIVO APP</span>
                    <select name="attivo_app" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['attivo_app'] ?? 'T') == 'T' ? ' selected' : ''; ?>>APP SI</option>
                        <option value="F"<?php echo ($rigaInModifica['attivo_app'] ?? '') == 'F' ? ' selected' : ''; ?>>APP NO</option>
                    </select>
                    </div>
                </div>
                <span class="admin-group-title">DALLA DATA</span>
                <input type="datetime-local" name="data_da" class="codice-text-field" required value="<?php echo htmlspecialchars(isset($rigaInModifica['data_da']) ? str_replace(' ', 'T', substr($rigaInModifica['data_da'], 0, 16)) : ''); ?>">
                <span class="admin-group-title">ALLA DATA</span>
                <input type="datetime-local" name="data_a" class="codice-text-field" required value="<?php echo htmlspecialchars(isset($rigaInModifica['data_a']) ? str_replace(' ', 'T', substr($rigaInModifica['data_a'], 0, 16)) : ''); ?>">
                <!-- Tastiera touch (variante A da issue, riuso): sotto la seconda data, focus-target NOME/LIMITE (int-only su LIMITE) -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kbc-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kbc-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-kbc-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=contatori" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-contatore">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form posizione: chiavi nascoste con valori originali per la update sulla tripla -->
            <?php elseif ($tabForm === 'prodotti_categorie'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-posizione" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="prodotti_categorie">
                <input type="hidden" name="is_edit" value="<?php echo $inModifica ? '1' : '0'; ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="oid_p" value="<?php echo $inModifica ? (int)$rigaInModifica['id_prodotto'] : 0; ?>">
                <input type="hidden" name="oid_c" value="<?php echo $inModifica ? (int)$rigaInModifica['id_categoria'] : 0; ?>">
                <input type="hidden" name="oid_pos" value="<?php echo $inModifica ? (int)$rigaInModifica['posizione'] : 0; ?>">
                <select name="id_prodotto" class="codice-text-field" required>
                    <option value="0">PRODOTTO…</option>
                    <?php if ($inModifica): $prodottoTrovato = false; ?>
                        <?php foreach ($opzioniProdotti as $opzione): if ((int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) $prodottoTrovato = true; endforeach; ?>
                        <?php if (!$prodottoTrovato): ?><option value="<?php echo (int)$rigaInModifica['id_prodotto']; ?>" selected>(ORFANO #<?php echo (int)$rigaInModifica['id_prodotto']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniProdotti as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_prodotto']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['descrizione_prod'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="id_categoria" class="codice-text-field" required>
                    <option value="0">CATEGORIA…</option>
                    <?php if ($inModifica): $categoriaTrovata = false; ?>
                        <?php foreach ($opzioniCategorie as $opzione): if ((int)$opzione['id_categoria'] === (int)$rigaInModifica['id_categoria']) $categoriaTrovata = true; endforeach; ?>
                        <?php if (!$categoriaTrovata): ?><option value="<?php echo (int)$rigaInModifica['id_categoria']; ?>" selected>(ORFANA #<?php echo (int)$rigaInModifica['id_categoria']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniCategorie as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_categoria']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_categoria'] === (int)$rigaInModifica['id_categoria']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['descrizione_cat'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="posizione" id="pos-val" value="<?php echo (int)($rigaInModifica['posizione'] ?? 0); ?>">
                <!-- Griglia posizioni 4x6 (issue): occupancy per categoria, click seleziona e sincronizza hidden -->
                <table class="pos-grid" id="pos-grid">
                    <tbody>
                        <?php for ($rigaGrid = 0; $rigaGrid < 6; $rigaGrid++): ?><tr>
                            <?php for ($colGrid = 1; $colGrid <= 4; $colGrid++): $numPos = $rigaGrid * 4 + $colGrid; ?>
                            <td><button type="button" class="bottone pos-cell" data-pos="<?php echo $numPos; ?>"><?php echo sprintf('%02d', $numPos); ?></button></td>
                            <?php endfor; ?>
                        </tr><?php endfor; ?>
                    </tbody>
                </table>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=prodotti_categorie" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-posizione">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form regola venduti: chiavi nascoste con valori originali per la update sulla coppia -->
            <?php elseif ($tabForm === 'prodotti_contatori'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-regola" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="prodotti_contatori">
                <input type="hidden" name="is_edit" value="<?php echo $inModifica ? '1' : '0'; ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="oid_c" value="<?php echo $inModifica ? (int)$rigaInModifica['id_contatore'] : 0; ?>">
                <input type="hidden" name="oid_p" value="<?php echo $inModifica ? (int)$rigaInModifica['id_prodotto'] : 0; ?>">
                <select name="id_contatore" class="codice-text-field" required>
                    <option value="0">VENDUTO…</option>
                    <?php if ($inModifica): $contatoreTrovato = false; ?>
                        <?php foreach ($opzioniContatori as $opzione): if ((int)$opzione['id_contatore'] === (int)$rigaInModifica['id_contatore']) $contatoreTrovato = true; endforeach; ?>
                        <?php if (!$contatoreTrovato): ?><option value="<?php echo (int)$rigaInModifica['id_contatore']; ?>" selected>(ORFANO #<?php echo (int)$rigaInModifica['id_contatore']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniContatori as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_contatore']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_contatore'] === (int)$rigaInModifica['id_contatore']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['nome'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="id_prodotto" class="codice-text-field" required>
                    <option value="0">PRODOTTO…</option>
                    <?php if ($inModifica): $prodottoTrovato = false; ?>
                        <?php foreach ($opzioniProdotti as $opzione): if ((int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) $prodottoTrovato = true; endforeach; ?>
                        <?php if (!$prodottoTrovato): ?><option value="<?php echo (int)$rigaInModifica['id_prodotto']; ?>" selected>(ORFANO #<?php echo (int)$rigaInModifica['id_prodotto']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniProdotti as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_prodotto']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['descrizione_prod'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantita" class="codice-text-field" step="1" min="0" required placeholder="QUANTITA DISPONIBILE" value="<?php echo htmlspecialchars($rigaInModifica['quantita'] ?? ''); ?>">
                <!-- Tastierino numerico int-only (issue, variante A): solo cifre + CANC sul campo quantita -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERINO NUMERICO</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('123', '456', '789', '0') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kbr-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === '0'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kbr-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=prodotti_contatori" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-regola">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form prodotto: ramo else perché prodotti è il tab di fallback (issue: solo PREZZO, barcode solo a fiera attiva, SVUOTA; tastiera come categorie con PREZZO double-only come QUANTITA in regole) -->
            <?php else: ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-prodotto" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="prodotti">
                <input type="hidden" name="id" value="<?php echo $inModifica ? (int)$rigaInModifica['id_prodotto'] : 0; ?>">
                <?php csrf_field(); ?>
                <input type="text" name="descrizione_prod" class="codice-text-field" maxlength="100" required placeholder="DESCRIZIONE" value="<?php echo htmlspecialchars($rigaInModifica['descrizione_prod'] ?? ''); ?>">
                <input type="text" name="testo_biglietto" class="codice-text-field" maxlength="100" placeholder="TESTO BIGLIETTO" value="<?php echo htmlspecialchars($rigaInModifica['testo_biglietto'] ?? ''); ?>">
                <input type="number" name="prezzo" class="codice-text-field" step="0.01" min="0" required placeholder="PREZZO" value="<?php echo htmlspecialchars($rigaInModifica['prezzo'] ?? ''); ?>">
                <div class="admin-btn-row" style="width:100%;">
                    <select name="olpp" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['olpp'] ?? 'T') == 'T' ? ' selected' : ''; ?>>OLPP T</option>
                        <option value="F"<?php echo ($rigaInModifica['olpp'] ?? '') == 'F' ? ' selected' : ''; ?>>OLPP F</option>
                    </select>
                    <?php if (fieraAttiva()): ?>
                    <input type="text" name="barcode" class="codice-text-field" maxlength="20" required placeholder="BARCODE" value="<?php echo htmlspecialchars($rigaInModifica['barcode'] ?? ''); ?>" style="flex:2;">
                    <?php else: ?>
                    <input type="hidden" name="barcode" value="<?php echo htmlspecialchars($rigaInModifica['barcode'] ?? '-'); ?>">
                    <?php endif; ?>
                </div>
                <!-- Tastiera touch (variante A da issue, come categorie): focus-target DESCRIZIONE/BIGLIETTO/PREZZO/BARCODE, double-only su PREZZO come QUANTITA in regole -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kbp-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kbp-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-kbp-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                            <button type="button" class="tastierino-btn" data-kbp-ch="." style="flex:1;height:48px;font-size:16px;">.</button>
                            <button type="button" class="tastierino-btn" data-kbp-ch="," style="flex:1;height:48px;font-size:16px;">,</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=prodotti" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-prodotto">SVUOTA CAMPI</button>
                </div>
            </form>
            <?php endif; ?>
        </section>
    </main>
<?php else: ?>
    <!-- Vista lista del tab corrente con ricerca, ordinamento e paginazione -->
    <main>
        <header><h1><?php echo $tabValido ? htmlspecialchars($tabConfig['titolo']) : 'TABELLA NON TROVATA'; ?></h1></header>
        <section class="admin-panel">
            <?php if (!$tabValido): ?>
                <h3 class="admin-group-title">TABELLA NON TROVATA — SCEGLI DALLA LISTA</h3>
            <?php else: ?>
                <h3 class="admin-group-title"><?php echo htmlspecialchars($tabConfig['label']); ?></h3>
                <?php if ($messaggioStato !== ''): ?>
                    <h3 class="admin-group-title"><?php echo htmlspecialchars($messaggioStato); ?></h3>
                <?php endif; ?>
                <!-- Ricerca GET: conserva tab e ordinamento, X azzera solo la query -->
                <form action="visualizza.php" method="get" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tabCorrente); ?>">
                    <?php if ($colonnaOrd !== ''): ?>
                        <input type="hidden" name="OR" value="<?php echo htmlspecialchars($colonnaOrd); ?>">
                        <input type="hidden" name="DIR" value="<?php echo $direzioneOrd; ?>">
                    <?php endif; ?>
                    <input type="text" id="q" name="q" class="codice-text-field" maxlength="50" placeholder="CERCA…" value="<?php echo htmlspecialchars($ricerca); ?>" style="margin-bottom:0;text-align:left;padding-left:12px;">
                    <button type="submit" class="opzione-btn" style="padding:12px 20px;">CERCA</button>
                    <?php if ($ricerca !== ''): ?>
                        <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;">X</a>
                    <?php endif; ?>
                </form>
                <!-- Tastierino touch: scrive nel campo ricerca senza tastiera fisica -->
                <details>
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:8px 16px;font-size:20px;">TASTIERA +</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $row): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($i = 0; $i < strlen($row); $i++): ?>
                                    <button type="button" class="tastierino-btn" data-ch="<?php echo $row[$i]; ?>" style="flex:1;height:36px;font-size:14px;"><?php echo $row[$i]; ?></button>
                                <?php endfor; ?>
                                <?php if ($row === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" id="kb-canc" style="flex:1.5;height:36px;font-size:14px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-ch=" " style="flex:1;height:36px;font-size:14px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <!-- Tabella: intestazioni cliccabili per ordinare, ogni click azzera la pagina -->
                <table class="modifica-table">
                    <thead><tr>
                        <?php foreach ($tabConfig['headers'] as $intestazione): ?>
                            <?php // $eOrdinata colonna ordinata, $prossimaDir direzione opposta per il prossimo click
                            $eOrdinata = ($colonnaOrd === $intestazione[0]); $prossimaDir = ($eOrdinata && $direzioneOrd === 'ASC') ? 'DESC' : 'ASC'; ?>
                            <th class="intestazione-tabella-descrizione"><?php echo htmlspecialchars($intestazione[1]); ?> <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'OR' => $intestazione[0], 'DIR' => $prossimaDir, 'page' => 1) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" style="font-size:22px;text-decoration:none;"><?php echo $eOrdinata ? ($direzioneOrd === 'ASC' ? '▲' : '▼') : '△'; ?></a></th>
                        <?php endforeach; ?>
                        <th class="intestazione-tabella-azioni">AZIONI</th>
                    </tr></thead>
                    <tbody>
                    <?php if (count($righe) === 0): ?>
                        <tr><td class="cella-tabella-descrizione" style="font-size:16px;" colspan="<?php echo count($tabConfig['headers']) + 1; ?>">NESSUNA RIGA</td></tr>
                    <?php endif; ?>
                    <!-- Righe: un ramo di colonne e azioni per ciascuno dei 5 tab -->
                    <?php foreach ($righe as $riga): ?>
                        <tr>
                        <?php if ($tabCorrente === 'categorie'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['descrizione_cat'] ?? '')); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars($riga['testo_bottone'] ?? ''); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars(etichettaColore($riga['colore'] ?? '')); ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'categorie', 'edit' => (int)$riga['id_categoria']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'categorie'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare <?php echo htmlspecialchars($riga['descrizione_cat'] ?? '', ENT_QUOTES); ?>?');"><input type="hidden" name="del" value="<?php echo (int)$riga['id_categoria']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'prodotti'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['descrizione_prod'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;">&euro; <?php echo number_format((float)$riga['prezzo'], 2, ',', '.'); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo $riga['olpp'] === 'T' ? '1:1' : '1:n'; ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti', 'edit' => (int)$riga['id_prodotto']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare <?php echo htmlspecialchars($riga['descrizione_prod'], ENT_QUOTES); ?>?');"><input type="hidden" name="del" value="<?php echo (int)$riga['id_prodotto']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'prodotti_categorie'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['prodotto'])); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars(strtoupper($riga['categoria'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['posizione']; ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_categorie', 'edit_p' => (int)$riga['id_prodotto'], 'edit_c' => (int)$riga['id_categoria'], 'edit_pos' => (int)$riga['posizione']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_categorie'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare questa POSIZIONE?');"><input type="hidden" name="del_p" value="<?php echo (int)$riga['id_prodotto']; ?>"><input type="hidden" name="del_c" value="<?php echo (int)$riga['id_categoria']; ?>"><input type="hidden" name="del_pos" value="<?php echo (int)$riga['posizione']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'contatori'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['nome'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['limite_qta']; ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo etichettaSiNo($riga['attivo']); ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'contatori', 'edit' => (int)$riga['id_contatore']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'contatori'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare <?php echo htmlspecialchars($riga['nome'], ENT_QUOTES); ?>?');"><input type="hidden" name="del" value="<?php echo (int)$riga['id_contatore']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'prodotti_contatori'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['contatore'])); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars(strtoupper($riga['prodotto'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['quantita']; ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_contatori', 'edit_c' => (int)$riga['id_contatore'], 'edit_p' => (int)$riga['id_prodotto']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_contatori'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare questa REGOLA?');"><input type="hidden" name="del_c" value="<?php echo (int)$riga['id_contatore']; ?>"><input type="hidden" name="del_p" value="<?php echo (int)$riga['id_prodotto']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Paginazione compatta: prima, corrente e vicine, conserva ricerca e ordinamento -->
                <div class="admin-btn-row">
                    <?php if ($paginaCorrente > 1): ?>
                        <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'page' => $paginaCorrente - 1) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;">&lt;</a>
                    <?php endif; ?>
                    <?php foreach (array_unique(array(1, $paginaCorrente - 1, $paginaCorrente, $paginaCorrente + 1, $totPagine)) as $numPagina): ?>
                        <?php if ($numPagina < 1 || $numPagina > $totPagine) continue; ?>
                        <?php if ($numPagina == $paginaCorrente): ?>
                            <span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;"><?php echo $numPagina; ?></span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'page' => $numPagina) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;"><?php echo $numPagina; ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($paginaCorrente < $totPagine): ?>
                        <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'page' => $paginaCorrente + 1) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;">&gt;</a>
                    <?php endif; ?>
                </div>
                <!-- Pulsante nuovo con etichetta diversa per ogni tab -->
                <?php $etichetteNuovo = array('categorie' => 'NUOVA CATEGORIA', 'prodotti' => 'NUOVO PRODOTTO', 'prodotti_categorie' => 'NUOVA POSIZIONE', 'contatori' => 'NUOVO VENDUTO', 'prodotti_contatori' => 'NUOVA REGOLA'); ?>
                <div class="admin-btn-row"><a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'nuovo' => 1))); ?>" class="opzione-btn"><?php echo $etichetteNuovo[$tabCorrente]; ?></a></div>
            <?php endif; ?>
        </section>
    </main>
    <!-- Nav verticale dei 5 tab, evidenzia quello corrente -->
    <aside>
        <h3 class="admin-group-title">TABELLA</h3>
        <nav style="flex-direction:column;align-items:stretch;">
            <?php foreach ($configTabs as $slugTab => $configTab): ?>
                <button onclick="location.href='<?php echo htmlspecialchars(urlLista(array('tab' => $slugTab)), ENT_QUOTES); ?>'"<?php echo ($tabValido && $slugTab === $tabCorrente) ? ' class="attivo"' : ''; ?>><?php echo htmlspecialchars($configTab['label']); ?></button>
            <?php endforeach; ?>
            <hr style="border:0;border-top:2px solid #e7e9eb;margin:16px 0;">
            <button onclick="location.href='statistiche.php'">STATISTICHE</button>
        </nav>
        <div class="admin-btn-row" style="margin-top:auto;"><a href="../index.php" class="opzione-btn">TORNA A SALSICCIA</a><a href="backup.php" class="opzione-btn">BACKUP</a><a href="visualizza.php?logout=1" class="opzione-btn">LOGOUT</a></div>
    </aside>
<?php endif; ?>
<script>
// Tastiera form categoria (issue, variante A da issue): scrive sul campo con focus,
// CANC = slice ultimo char, SVUOTA = form.reset() senza submit.
(function()
{
    var f = document.getElementById('form-categoria');
    if (!f) return;
    var campi = f.querySelectorAll('input[type=text]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kb-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            target.value += b.getAttribute('data-kb-ch'); target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kb-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-campi');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Tastiera form prodotto (variante A come categorie, riuso): focus-target DESCRIZIONE/BIGLIETTO/PREZZO/BARCODE, double-only su PREZZO come QUANTITA in regole, SVUOTA reset.
(function()
{
    var f = document.getElementById('form-prodotto');
    if (!f) return;
    var campi = f.querySelectorAll('input[name=descrizione_prod],input[name=testo_biglietto],input[name=prezzo],input[name=barcode]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbp-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            var ch = b.getAttribute('data-kbp-ch');
            if (target.name === 'prezzo')
            {
                if (ch === ',') ch = '.';
                if (ch === '.')
                {
                    if (target.value.indexOf('.') !== -1) return;
                    ch = target.value === '' ? '0.' : '.';
                }
                else if (!/[0-9]/.test(ch)) return;
            }
            target.value += ch; target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbp-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-prodotto');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Tastiera form contatore (issue, variante A riuso): focus-target NOME/LIMITE, int-only su LIMITE, SVUOTA reset.
(function()
{
    var f = document.getElementById('form-contatore');
    if (!f) return;
    var campi = f.querySelectorAll('input[name=nome],input[name=limite_qta]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbc-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            var ch = b.getAttribute('data-kbc-ch');
            if (target.name === 'limite_qta' && !/[0-9]/.test(ch)) return;
            target.value += ch; target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbc-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-contatore');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Tastierino numerico form regola (issue, variante A): solo cifre su quantita, SVUOTA reset.
(function()
{
    var f = document.getElementById('form-regola');
    if (!f) return;
    var target = f.querySelector('input[name=quantita]');
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbr-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            target.value += b.getAttribute('data-kbr-ch'); target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbr-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-regola');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Griglia posizioni 4x6 (issue): verde libera / rossa occupata per categoria, click = gialla + hidden sincronizzato, SVUOTA resetta anche la griglia.
(function()
{
    var f = document.getElementById('form-posizione');
    if (!f) return;
    var val = document.getElementById('pos-val');
    var cat = f.querySelector('select[name=id_categoria]');
    var celle = Array.prototype.slice.call(f.querySelectorAll('#pos-grid [data-pos]'));
    var occ = <?php echo json_encode($mappaPosizioni); ?>;
    function render()
    {
        var sel = parseInt(val.value || '0', 10);
        var piene = {};
        Array.prototype.forEach.call(occ[cat.value] || [], function(p) { piene[parseInt(p, 10)] = 1; });
        Array.prototype.forEach.call(celle, function(b)
        {
            var p = parseInt(b.getAttribute('data-pos'), 10);
            b.classList.remove('pos-libera', 'pos-occupata', 'pos-selezionata');
            if (p === sel && sel >= 1 && sel <= 24) b.classList.add('pos-selezionata');
            else if (piene[p]) b.classList.add('pos-occupata');
            else b.classList.add('pos-libera');
        });
    }
    Array.prototype.forEach.call(celle, function(b)
    {
        b.addEventListener('click', function() { val.value = b.getAttribute('data-pos'); render(); });
    });
    if (cat) cat.addEventListener('change', render);
    var sv = document.getElementById('svuota-posizione');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset(); val.value = '0'; render();
        var s = f.querySelector('select'); if (s) s.focus();
    });
    render();
})();
</script>
<script>

// Tastierino: accoda il carattere al campo ricerca e ridà il focus
document.querySelectorAll('.tastierino-btn[data-ch]').forEach(function(b)
{
    b.addEventListener('click', function()
    {
        var i = document.getElementById('q');
        if (i)
        {
            i.value += b.getAttribute('data-ch'); i.focus();
        }
    });
});
 // Tasto CANC: cancella l'ultimo carattere della ricerca
var kc = document.getElementById('kb-canc');

if (kc) kc.addEventListener('click', function()
{
    var i = document.getElementById('q');
    if (i)
    {
        i.value = i.value.slice(0, -1); i.focus();
    }
});
</script>
</body>
</html>
