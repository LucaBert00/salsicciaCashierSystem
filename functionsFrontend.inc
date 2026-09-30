<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successori parziali esistenti src/Cassa/OrderService.php + src/Catalog/* + src/Cassa/PrintService.php + src/Fiscale/Fiscale.php; resto pianificato in src/{Cassa,Printer,System,Config}/ (v. docs/ARCHITETTURA_REVISTA.md §6 e §9, fasi 1-3); rimozione solo a migrazione completa, rivalutare entro 2026-12-31.

require_once __DIR__ . '/set.inc';
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/funzioni.inc';
require_once __DIR__ . '/src/Fiscale/Fiscale.php';
require_once __DIR__ . '/src/Cassa/PrintService.php';
require_once __DIR__ . '/src/Cassa/OrderService.php';
require_once __DIR__ . '/src/Cassa/OrderType.php';
require_once __DIR__ . '/src/Cassa/PayMethod.php';
require_once __DIR__ . '/src/Catalog/CatalogRepo.php';
require_once __DIR__ . '/src/Catalog/CatalogView.php';

// Auth admin cassa (issue): sessione come reserved/login.php, mai code in URL.
salsiccia_session_start();

function isAdmin()
{
    return !empty($_SESSION['admin']);
}

// Cassa del terminale: scelta esplicita in sessione (pannello admin) se valida,
// altrimenti fallback legacy da IP in set.inc (fiera: nessuna config extra).
function cassaCorrente()
{
    if (isset($_SESSION['id_cassa']))
    {
        $c = (int)$_SESSION['id_cassa'];
        if ($c >= 1 && $c <= 999)
            return $c;
    }
    return ID_CASSA;
}

// Default categoria da .env SALSICCIA_DEFAULT_CAT: id numerico o nome (case-insensitive, messo in CAPS).
// ?cat= esplicito vince sempre; senza -> env -> MIN(id) -> DEFAULT_CAT.
function defaultCat($mysqli)
{
    $raw = trim((string)(getenv('SALSICCIA_DEFAULT_CAT') ?: ''));
    // T19: letture catalogo via CatalogRepo (prepared), qui resta solo env.
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    if ($raw !== '')
    {
        if (ctype_digit($raw) && (int)$raw > 0)
            return (int)$raw; // fiducia all'env, niente query extra
        $trovato = $repo->trovaIdPerNome(strtoupper($raw));
        if ($trovato !== null)
            return $trovato;
    }
    $min = $repo->minCategoriaId();
    if ($min !== null)
        return $min;
    return DEFAULT_CAT;
}

$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
if ($cat <= 0)
    $cat = defaultCat($mysqli);

$id_cassa = cassaCorrente();

$but_x_row = BOT_X_ROW;
$but_x_col = BOT_X_COL;
$bot_width = BOT_WIDTH;
$bot_height = BOT_HEIGHT;

// Azioni del riepilogo (T30: DB/cassa iniettati, mai globali).
gestisciAzioni($mysqli, $id_cassa);

// Modalita' fiera (issue, consumata in): "1" = barcode visibile/obbligatorio, "0" = nascosto con default '-'.
function isFieraAttiva()
{
    return cassa_leggi_fiera();
}

// T17: scrive il flag in storage/cassa_flags.json via cassa_imposta_fiera()
// (atomico, concorrente-safe); set.inc resta congelato read-only, mai rewrite.
function impostaModalitaFiera($valore)
{
    cassa_imposta_fiera($valore === "1" ? "1" : "0");
}

// F3.3 #63: persistenza toggle carta in storage/carta_stampa.mode (monoriga "1"/"0"),
// letta in set.inc con precedenza env > file > gate.
function impostaCartaStampa($valore)
{
    $v = $valore === "1" ? "1" : "0";
    if (file_put_contents(salsiccia_storage_path('carta_stampa.mode'), $v . "\n", LOCK_EX) === false)
    {
        cassa_log('error', "carta_stampa.mode: scrittura fallita");
        return false;
    }
    return true;
}

// Hex definitivi categorie cassa (decisi da Chiara in issue: 14 pastelli, niente nero,
// testo #2b3d4e sempre leggibile). Unico punto di verita' per i colori cassa;
// DB invariato (categorie.colore resta '01'..'14', v. mappaColori in reserved/visualizza.php).
// Ignoti -> bottone default.
// Verifica: contrasto #2b3d4e 13/14 >= 4.5 OK, 12 #EB7BCB 4.38 AA-large (bottoni bold 14px + prezzo 26px);
// ?cat=17/19/20 (02/03/05) 6.38/8.28/8.60 OK. Scope = A solo-bottoni, main #e7e9eb invariato.
function coloreCategoriaHex($codice)
{
    $c = strtoupper(trim((string)$codice));
    if (ctype_digit($c))
    {
        $c = sprintf('%02d', (int)$c);
    }
    static $hex = array(
        '01' => '#F58C8C', // ROSSO
        '02' => '#FFB37D', // ARANCIONE
        '03' => '#EDDEA2', // OCRA
        '04' => '#FFF296', // GIALLO
        '05' => '#C4EF9B', // VERDE
        '06' => '#AFCEB9', // VERDE SCURO
        '07' => '#BBF7F5', // AZZURRO
        '08' => '#8AC7F5', // BLU
        '09' => '#B39BE6', // VIOLA
        '10' => '#CACDFF', // LILLA
        '11' => '#E396F4', // VIOLA CHIARO
        '12' => '#EB7BCB', // ROSA SCURO
        '13' => '#FFA5D6', // ROSA
        '14' => '#FFD2D8', // ROSA CHIARO
    );
    return isset($hex[$c]) ? $hex[$c] : '#FFFFFF';
}

// T23: mutazioni cassa come handler nominati, dispatch via routes/cassa.php.
// Corpi verbatim dai rami del vecchio if-chain (stessi guard POST/CSRF/redirect,
// stessa semantica T05/T06/T08/T14); sparita solo la catena ($action ==).
// Flussi ordine (T18): logica in Salsiccia\Cassa\OrderService con DB iniettato;
// qui resta solo HTTP/CSRF/redirect (thin caller).
// T14: mutazioni cassa solo via POST con token; GET resta lettura:
// prefetch/link non mutano mai l'ordine live ($post_ok ricalcolato per handler).
function cassa_azione_login($mysqli = null, $id_cassa = null)
{

    // Login admin via POST (issue): code mai in URL/log; OK -> flag sessione + redirect pulito.
    if (($_SERVER['REQUEST_METHOD'] ?? '') == 'POST' && ($_GET['action'] ?? '') == 'c' && isset($_POST['code']))
    {
        // Tentativo login: invalida la sessione admin precedente, err=1 non deve mai coesistere col pannello.
        unset($_SESSION['admin']);
        // T28: throttle server-side scope 'admin' (file, sopravvive al reset
        // sessione) + ramo sessione storico; kiosk invariato.
        if ((!empty($_SESSION['admin_block_until']) && time() < $_SESSION['admin_block_until']) || salsiccia_login_throttled('admin'))
        {
            header("Location: index.php?action=c&ok=1&err=1");
            exit;
        }
        if (password_verify((string)$_POST['code'], (string)PWD_HASH))
        {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            unset($_SESSION['admin_fail'], $_SESSION['admin_block_until']);
            salsiccia_login_ok('admin');
            header("Location: index.php?action=c&ok=1");
        }
        else
        {
            $_SESSION['admin_fail'] = isset($_SESSION['admin_fail']) ? (int)$_SESSION['admin_fail'] + 1 : 1;
            if ($_SESSION['admin_fail'] >= 5)
            {
                $_SESSION['admin_block_until'] = time() + 60;
                unset($_SESSION['admin_fail']);
            }
            salsiccia_login_fail('admin');
            header("Location: index.php?action=c&ok=1&err=1");
        }
        exit;
    }
}

function cassa_azione_logout($mysqli = null, $id_cassa = null)
{

    if (isset($_GET['action']) && $_GET['action'] == 'logout')
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
        header("Location: index.php");
        exit;
    }
}

function cassa_azione_cassa($mysqli = null, $id_cassa = null)
{
    // Scelta esplicita cassa da pannello admin: salva in sessione, mai da IP.
    if (($_SERVER['REQUEST_METHOD'] ?? '') == 'POST' && ($_GET['action'] ?? '') == 'cassa' && isset($_POST['id_cassa']))
    {
        if (!isAdmin() || !csrf_ok())
        {
            header("Location: index.php?action=c");
            exit;
        }
        $c = (int)$_POST['id_cassa'];
        if ($c >= 1 && $c <= 999)
            $_SESSION['id_cassa'] = $c;
        header("Location: index.php?action=c&ok=1");
        exit;
    }
}

function cassa_azione_fiera($mysqli = null, $id_cassa = null)
{

    // Toggle fiera da pannello admin: solo POST con token, GET resta lettura.
    if (($_GET['action'] ?? '') == 'fiera')
    {
        if (!isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') == 'POST' && csrf_ok() && isset($_POST['val']))
            impostaModalitaFiera($_POST['val'] === "1" ? "1" : "0");
        header("Location: index.php");
        exit;
    }
}

function cassa_azione_annulla($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok) // annulla ordine
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->annullaOrdine();
    }
}

function cassa_azione_aggiungi($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok && isset($_GET['id'])) // aggiungi prodotto
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->aggiungiProdotto((int)$_GET['id']);
    }
}

// Kiosk barcode (#46, da a49af04): scanner keyboard-wedge -> POST action=b bc=.
// T14 (a): mutazione solo via POST+CSRF come la griglia prodotti, GET ignorato.
// Fiera spenta: ignorato verbatim (nessun ordine, nessun messaggio).
// T18: risolve barcode->id poi delega a OrderService::aggiungiProdotto(), mai INSERT qui.
// T30: firma ($mysqli, $id_cassa), niente $GLOBALS, Repo/View senza superglobali.
function cassa_azione_barcode($mysqli, $id_cassa)
{
    if (!isFieraAttiva())
    {
        return;
    }
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if (!$post_ok || !isset($_POST['bc']))
    {
        return;
    }
    $raw = substr(trim((string)$_POST['bc']), 0, 20);
    if ($raw === '' || $raw === '-')
    {
        return;
    }
    $id = (new \Salsiccia\Catalog\CatalogRepo($mysqli))->trovaIdPerBarcode($raw);
    if ($id !== null && $id > 0)
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->aggiungiProdotto($id);
    }
    // Ignoto: nessun ordine, banner in mostraBarraBarcode() dallo stesso POST.
}

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

function cassa_azione_rimuovi($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok && isset($_GET['id']))
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->rimuoviRiga((int)$_GET['id']);

        header("Location: ?action=m");
        exit;
    }
}

function cassa_azione_tipo($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok && isset($_GET['t']))
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->impostaTipo((string)$_GET['t']);

        header("Location: ?action=o");
        exit;
    }
}

function cassa_azione_standby($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok) // set ordine in standby
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->mettiStandby();
        header("Location: index.php");
        exit;
    }
}

function cassa_azione_riattiva($mysqli, $id_cassa)
{
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();
    if ($post_ok && isset($_GET['id'])) // reactivate da standby
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->riattiva((int)$_GET['id']);
        header("Location: index.php");
        exit;
    }
}

function gestisciAzioni($mysqli, $id_cassa)
{

    // T23: dispatch via tabella (routes/cassa.php, nessun if); action non
    // stringa o non listata = schermata sola lettura o errore kiosk in
    // public/index.php, mai fatal. Handler null = sola lettura.
    // T30: ogni handler ha la stessa interfaccia ($mysqli, $id_cassa);
    // chi non usa il DB li dichiara opzionali e li ignora.
    static $tabella = null;
    if ($tabella === null)
        $tabella = (array)require __DIR__ . '/routes/cassa.php';
    $action = $_GET['action'] ?? '';
    if (!is_string($action) || $action === '' || !array_key_exists($action, $tabella))
        return;
    $handler = $tabella[$action];
    if (!is_string($handler) || $handler === '' || !function_exists($handler))
        return;
    $handler($mysqli, $id_cassa);
}

// pulsanti categorie (T19: thin caller, query in CatalogRepo, HTML in CatalogView).
function mostraNavCategorie($mysqli, $cat)
{
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    \Salsiccia\Catalog\CatalogView::navCategorie($repo->allCategorie(), (int)$cat);
}

//Categoria corrente (T19: thin caller).
function mostraTitolo($mysqli, $cat)
{
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    \Salsiccia\Catalog\CatalogView::titolo($repo->descrizioneCategoria((int)$cat));
}

//indicatore tipo ordine nav (T26: lettere da OrderType enum, unica fonte)
function mostraIndicatoreTipo($mysqli)
{
    $id_cassa = cassaCorrente();

    $ris = db_select($mysqli, "SELECT tipo FROM ordini WHERE id_cassa = ? AND chiuso = '0' LIMIT 1", 'i', array($id_cassa));

    if ($ris && mysqli_num_rows($ris) > 0)
    {
        $riga = mysqli_fetch_array($ris);
        $tipo = \Salsiccia\Cassa\OrderType::tryFrom((string)$riga['tipo']);
        $lett = $tipo ? $tipo->lettera() : null;

        if ($lett !== null)
            echo '<span style="color:#e54b3c;font-weight:800;font-size:18px;margin-left:auto;">' . $lett . '</span>';
    }
}

// tabella prodotti (T19: thin caller, query in CatalogRepo, HTML in CatalogView).
function mostraTabellaProdotti($mysqli, $cat, $but_x_row, $but_x_col)
{
    $cat = (int)$cat;
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    $prodotti = $repo->prodottiByCategoria($cat);
    $vuota = count($prodotti) === 0 ? $repo->categoria($cat) : null;
    \Salsiccia\Catalog\CatalogView::tabellaProdotti($prodotti, $vuota, $cat, (int)$but_x_row, (int)$but_x_col);
}

// Barra barcode kiosk (#46, da a49af04): solo a fiera attiva, su schermata cassa.
// T19 thin caller: lookup in CatalogRepo, HTML in CatalogView, zero query in View.
// T30: errore ignoto come parametro, mai $GLOBALS.
function mostraBarraBarcode($mysqli, $cat)
{
    if (!isFieraAttiva())
    {
        return;
    }
    $errore = '';
    if ((($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok() && isset($_POST['bc']))
    {
        $raw = substr(trim((string)$_POST['bc']), 0, 20);
        if ($raw !== '' && $raw !== '-')
        {
            $id = (new \Salsiccia\Catalog\CatalogRepo($mysqli))->trovaIdPerBarcode($raw);
            if ($id === null)
            {
                $errore = $raw;
            }
        }
    }
    \Salsiccia\Catalog\CatalogView::barraBarcode((int)$cat, $errore);
}

//Modifica ordine
function mostraModificaOrdine($mysqli)
{
    $id_cassa = cassaCorrente();

    $ris = db_select($mysqli, "SELECT prodotti.id_prodotto AS id, prodotti.descrizione_prod, righe_ordini.quantita, righe_ordini.totale, ordini.totale as tot_ord, ordini.id_ordine AS id_ord FROM prodotti, ordini, righe_ordini WHERE ordini.id_ordine = righe_ordini.id_ordine AND prodotti.id_prodotto = righe_ordini.id_prodotto AND id_cassa = ? AND ordini.chiuso = 0", 'i', array($id_cassa));

    echo '<section class="modifica-section">';

    if ($ris && mysqli_num_rows($ris) > 0)
    {
        echo '<table class="modifica-table">';
        echo '<thead><tr>';
        echo '<th class="intestazione-tabella-descrizione">DESCRIZIONE</th>';
        echo '<th class="intestazione-tabella-quantita">Q.TÀ</th>';
        echo '<th class="intestazione-tabella-prezzo">COSTO UNITARIO</th>';
        echo '<th class="intestazione-tabella-azioni">AZIONI</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        while ($riga = mysqli_fetch_array($ris))
        {
            $desc = htmlspecialchars(strtoupper($riga['descrizione_prod']), ENT_QUOTES, 'UTF-8');
            $prezzo_unitario = $riga['quantita'] > 0 ? number_format($riga['totale'] / $riga['quantita'], 2, ',', '.') : '0,00';
            $qta = (int)$riga['quantita'];
            $id_riga = (int)$riga['id'];

            echo '<tr>';
            echo "<td class=\"cella-tabella-descrizione\">{$desc}</td>";
            echo '<td class="cella-tabella-quantita">';
            echo '<div class="contenitore-qta">';
            if ($qta > 1)
            {
                echo "<form method=\"post\" action=\"?action=mq&id={$id_riga}&qta=" . ($qta - 1) . "\" style=\"display:contents;\">";
                csrf_field();
                echo "<button type=\"submit\" class=\"bottone-qta bottone-qta-meno\">−</button></form>";
            }
            else
                echo "<span class=\"bottone-qta bottone-qta-meno\" style=\"opacity:0.3;cursor:default\">−</span>";
            echo "<span class=\"input-qta\" style=\"display:inline-flex;align-items:center;justify-content:center\">{$qta}</span>";
            echo "<form method=\"post\" action=\"?action=mq&id={$id_riga}&qta=" . ($qta + 1) . "\" style=\"display:contents;\">";
            csrf_field();
            echo "<button type=\"submit\" class=\"bottone-qta bottone-qta-piu\">+</button></form>";
            echo '</div>';
            echo '</td>';
            echo "<td class=\"cella-tabella-prezzo\">€ {$prezzo_unitario}</td>";
            echo '<td class="cella-tabella-azioni">';
            echo "<a href=\"?action=m\" class=\"action-btn action-check\" style=\"text-decoration:none\">✓</a>";
            echo "<form method=\"post\" action=\"?action=mr&id={$id_riga}\" style=\"display:contents;\">";
            csrf_field();
            echo "<button type=\"submit\" class=\"action-btn action-delete\">✕</button></form>";
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }
    else
    {
        echo '<p style="text-align:center;margin-top:40px;font-size:18px;color:#8c9ba5;">Nessun prodotto nell\'ordine</p>';
    }

    echo '</section>';
}

// TAGLIA visibile solo con GX420t + carta continua (ZPL o EPL): in
// biglietti singoli il pulsante non appare mai e il taglio non scatta mai.
function tagliaVisibile()
{
    return defined('PRINTER_NAME') && defined('PRINTER_CONNECTION')
        && defined('PRINTER_LANGUAGE') && defined('CONTINUOUS_LABEL')
        && function_exists('printer_taglia_permesso')
        && printer_taglia_permesso(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE)
        && CONTINUOUS_LABEL && (PRINTER_LANGUAGE === 'ZPL' || PRINTER_LANGUAGE === 'EPL');
}

// Footer modifica
function mostraFooterModifica($cat)
{
    echo '<form method="post" action="?cat=' . (int)$cat . '&action=r" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" id="annulla-btn"><span id="annullaOrdine-sign">🗑️️ ANNULLA ORDINE</span></button></form>';
    if (tagliaVisibile())
        echo '<a href="?cat=' . (int)$cat . '&action=s&taglia=1" id="taglia-btn" style="text-decoration:none"><span id="taglia-sign">✂️ TAGLIA</span></a>';
    echo '<a href="index.php" id="continua-btn" style="text-decoration:none"><span id="continua-sign">🛒 CONTINUA ORDINE</span></a>
          <a href="?action=s" id="stampa-btn" style="text-decoration:none"><span id="stampa-sign">🖨️ STAMPA</span></a>';
}

// schermata opzioni (T26: bottoni da OrderType enum, unica fonte)
function mostraOpzioni()
{
    echo '<section class="opzioni-section">';
    echo '<div class="opzioni-row-1">';
    foreach (array(\Salsiccia\Cassa\OrderType::Prevendita, \Salsiccia\Cassa\OrderType::Musicisti, \Salsiccia\Cassa\OrderType::Staff, \Salsiccia\Cassa\OrderType::Asporto) as $t)
    {
        echo '<form method="post" action="?action=st&t=' . $t->value . '" style="display:contents;">';
        csrf_field();
        echo '<button type="submit" class="opzione-btn">' . $t->label() . '</button></form>';
    }
    echo '</div>';
    echo '<div class="opzioni-row-2">';
    echo '<form method="post" action="?action=st&t=' . \Salsiccia\Cassa\OrderType::Normale->value . '" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" class="opzione-btn opzione-btn-large">' . \Salsiccia\Cassa\OrderType::Normale->label() . '</button></form>';
    echo '<form method="post" action="?action=sb" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" class="opzione-btn opzione-btn-large">STAND BY</button></form>';
    echo '</div>';
    echo '</section>';
}

function mostraOrdiniStandby($mysqli)
{
    $query = "SELECT * FROM ordini WHERE chiuso = 'S'";
    $ris = mysql_query_safe($mysqli, $query);

    echo '<section class="standby-section">';

    if (mysqli_num_rows($ris) > 0)
    {
        echo '<table class="standby-table">';
        echo '<thead><tr>';
        echo '<th>Ordine n.</th>';
        echo '<th>Data</th>';
        echo '<th>Totale</th>';
        echo '<th>Act.</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        while ($riga = mysqli_fetch_array($ris))
        {
            $data = date('d/m/Y H:i:s', strtotime($riga['data_ora']));
            $totale = number_format((float)$riga['totale'], 2, ',', '.') . '&euro;';
            $id_standby = (int)$riga['id_ordine'];
            echo '<tr>';
            echo "<td>{$id_standby}</td>";
            echo "<td>{$data}</td>";
            echo "<td>{$totale}</td>";
            echo '<td><form method="post" action="?action=ra&id=' . $id_standby . '" style="display:contents;">';
            csrf_field();
            echo '<button type="submit" class="standby-attiva-btn">&#9998;</button></form></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    }
    else
    {
        echo '<p class="standby-empty">Nessun ordine in standby</p>';
    }

    echo '</section>';
}

// Pulsanti annulla ordine e stampa
function mostraFooterBottoni($cat)
{
    echo '<form method="post" action="?cat=' . (int)$cat . '&action=r" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" id="annulla-btn"><span id="annullaOrdine-sign">🗑️️ ANNULLA ORDINE</span></button></form>';
    if (tagliaVisibile())
        echo '<button id="taglia-btn" onclick="location.href=\'?cat=' . (int)$cat . '&action=s&taglia=1\'"><span id="taglia-sign">✂️ TAGLIA</span></button>';
    echo '<button id="stampa-btn" onclick="location.href=\'?action=s\'"><span id="stampa-sign">🖨️ STAMPA</span></button>';
}

//R iepilogo ordine
function mostraBoxRiepilogo($mysqli)
{
    $id_cassa = cassaCorrente();

    $ris = db_select($mysqli, "SELECT id_ordine, totale FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($id_cassa));

    if ($ris && mysqli_num_rows($ris) > 0)
    {
        $riga = mysqli_fetch_array($ris);
        $totale = number_format((float)$riga['totale'], 2, ',', '.');
        $id_ordine_box = (int)$riga['id_ordine'];
        echo "<div class=\"order-number\">ORDINE #{$id_ordine_box}</div>
              <div class=\"order-total\">
                <h3>TOTALE DA PAGARE</h3>
                <h1 class=\"item-cost\">&euro; {$totale}</h1>
              </div>";
    }
    else
    {
        echo '<div class="order-number">NESSUN ORDINE APERTO</div>
              <div class="order-total">
                <h3>TOTALE DA PAGARE</h3>
                <h1 class="item-cost">&euro; 0,00</h1>
              </div>';
    }
}

//Pulsanti modifica e opzioni
function mostraAzioniLaterali()
{
    echo '<a href="?action=m" class="aside-btn" style="text-decoration:none">🖊️ Modifica Ordine</a>
          <a href="?action=o" class="aside-btn" style="text-decoration:none">⚙️ Opzioni</a>';
}

// elenco prodotti ordine
function mostraListaProdotti($mysqli)
{
    $id_cassa = cassaCorrente();

    $ris = db_select($mysqli, "SELECT prodotti.descrizione_prod, righe_ordini.quantita, righe_ordini.totale FROM prodotti, ordini, righe_ordini WHERE ordini.id_ordine = righe_ordini.id_ordine AND prodotti.id_prodotto = righe_ordini.id_prodotto AND id_cassa = ? AND ordini.chiuso = '0'", 'i', array($id_cassa));

    if ($ris && mysqli_num_rows($ris) > 0)
    {
        while ($riga = mysqli_fetch_array($ris))
        {
            $prezzo = number_format((float)$riga['totale'], 2, ',', '.');
            $desc = htmlspecialchars(strtoupper($riga['descrizione_prod']), ENT_QUOTES, 'UTF-8');
            $qta_lista = (int)$riga['quantita'];
            echo "<li>
                    <div class=\"cart-item\">
                        <span class=\"item-qty\">{$qta_lista}</span>
                        <span class=\"item-desc\">{$desc}</span>
                        <span class=\"item-cost\">&euro; {$prezzo}</span>
                    </div>
                  </li>";
        }
    }
    else
    {
        echo '<li><div class="cart-item"><span class="item-desc">Nessun prodotto nell\'ordine</span></div></li>';
    }
}

//pulsanti fondo aside
function mostraAzioniExtra()
{
    echo '<div style="margin-top:auto;display:flex;gap:12px;padding-top:20px;">';
    echo '<a href="?action=c" class="aside-btn" style="text-decoration:none">🔧 Configurazione</a>';
    echo '<a href="?action=standby" class="aside-btn" style="text-decoration:none">⏸️ Ordini in standby</a>';
    echo '</div>';
}

// Pannello configurazione con tastierino: code solo via POST in sessione (issue),
// tasti via JS come tastiera touch di reserved/login.php cosi nulla finisce in URL/log.
function mostraPannelloConfig()
{
    echo '<h3 style="font-size:13px;color:#5a6b7c;margin-bottom:12px;font-weight:800;">INSERISCI IL CODICE:</h3>';
    echo '<form method="post" action="?action=c&ok=1" id="form-admin-code">';
    echo '<input type="password" name="code" id="admin-code" class="codice-text-field" value="" readonly required>';

    echo '<div class="tastierino-grid">';
    for ($i = 1; $i <= 9; $i++)
        echo '<button type="button" class="tastierino-btn" data-kb-ch="' . $i . '">' . $i . '</button>';
    echo '<button type="button" class="tastierino-btn" data-kb-ch="*">*</button>';
    echo '<button type="button" class="tastierino-btn" data-kb-ch="0">0</button>';
    echo '<button type="button" class="tastierino-btn" data-kb-ch="@">@</button>';
    echo '</div>';

    echo '<div class="tastierino-actions">';
    echo '<button type="submit" class="tastierino-action-btn action-ok" style="display:flex;align-items:center;justify-content:center">OK</button>';
    echo '<button type="button" class="tastierino-action-btn action-canc" data-kb-canc style="display:flex;align-items:center;justify-content:center">CANC</button>';
    echo '</div>';

    echo '<a href="index.php" class="tastierino-close-btn" style="text-decoration:none;display:flex;align-items:center;justify-content:center">CHIUDI</a>';
    echo '</form>';
    echo '<script>(function(){var i=document.getElementById("admin-code");if(!i)return;var f=document.getElementById("form-admin-code");Array.prototype.forEach.call(f.querySelectorAll("[data-kb-ch]"),function(b){b.addEventListener("click",function(){i.value+=b.getAttribute("data-kb-ch");});});Array.prototype.forEach.call(f.querySelectorAll("[data-kb-canc]"),function(b){b.addEventListener("click",function(){i.value="";});});})();</script>';
}

//Pannello admin
function mostraPannelloAdmin()
{
    echo '<section class="admin-panel">';

    echo '<div>';
    echo '<h3 class="admin-group-title">GESTIONE</h3>';
    echo '<div class="admin-btn-row">';
    // ingresso diretto reserved/, contratto sessione
    echo '<a href="reserved/login.php" class="opzione-btn">GESTIONE PRODOTTI</a>';
    echo '<a href="?action=repair" class="opzione-btn">RIPRISTINA DB</a>';
    echo '<a href="?action=info" class="opzione-btn">MODIFICA FESTA</a>';
    echo '<form method="post" action="?action=fiera" onsubmit="return confirm(\'Cambiare MODALITA FIERA?\');" style="display:inline;">';
    csrf_field();
    echo '<input type="hidden" name="val" value="' . (isFieraAttiva() ? '0' : '1') . '">';
    echo '<button type="submit" class="opzione-btn">FIERA: ' . (isFieraAttiva() ? 'ON' : 'OFF') . '</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    echo '<div>';
    echo '<h3 class="admin-group-title">CASSA TERMINALE: ' . cassaCorrente() . ' (default da IP: ' . ID_CASSA . ')</h3>';
    echo '<form method="post" action="?action=cassa" class="admin-btn-row" style="align-items:center;">';
    csrf_field();
    echo '<input type="number" name="id_cassa" value="' . cassaCorrente() . '" min="1" max="999" class="codice-text-field" style="max-width:160px;">';
    echo '<button type="submit" class="opzione-btn">IMPOSTA CASSA</button>';
    echo '</form>';
    echo '</div>';

    echo '<div>';
    echo '<h3 class="admin-group-title">PRODOTTI VENDUTI</h3>';
    echo '<div class="admin-btn-row">';
    echo '<a href="?action=contatori" class="opzione-btn">CONTATORI</a>';
    echo '</div>';
    echo '</div>';

    echo '<div>';
    echo '<h3 class="admin-group-title">AMMINISTRAZIONE</h3>';
    echo '<div class="admin-btn-row">';
    echo '<a href="index.php" class="opzione-btn">CONTINUA ORDINE</a>';
    echo '<a href="?action=logout" class="opzione-btn">ESCI</a>';
    echo '</div>';
    echo '<div class="admin-btn-row" style="margin-top:clamp(6px, 0.9765625vmin, 12px);">';
    echo '<a href="?action=print_reset" class="opzione-btn">CAMBIA CARTA STAMPA: ' . ((defined('CONTINUOUS_LABEL') && CONTINUOUS_LABEL) ? 'CONTINUA' : 'SINGOLI') . '</a>';
    echo '<a href="?action=switch_printer" class="opzione-btn">CAMBIA STAMPANTE</a>';
    echo '<a href="?action=restart" class="opzione-btn danger">RIAVVIA</a>';
    echo '<a href="?action=shutdown" class="opzione-btn danger">SPEGNI</a>';
    echo '</div>';
    echo '</div>';

    echo '</section>';
}

// F2.1 #68: preflight e funzioni pure power (da salsicciaStagisti 6b796de+d57a201).
// Alimentazione: system() sul fork ritorna 0 anche se fallisce dopo, mai verde falso.
// Candidati legacy-first (systemctl poi shutdown), preflight con "sudo -n -l" sul comando esatto.
// Il primo permesso viene schedulato, altrimenti COMANDO NON AVVIATO. Marker in storage/
// (mai path di directory del file: docroot target = public/), riscontro marker/uptime sotto.
function candidatiAlimentazione($mode)
{
    $aSys = ($mode === 'poweroff') ? 'poweroff' : 'reboot';
    $aShut = ($mode === 'poweroff') ? '-h now' : '-r now';
    $c = array();
    foreach (array('/bin/systemctl', '/usr/bin/systemctl') as $s)
        if (is_executable($s))
            $c[] = $s . ' ' . $aSys;
    foreach (array('/sbin/shutdown', '/usr/sbin/shutdown') as $s)
        if (is_executable($s))
            $c[] = $s . ' ' . $aShut;
    if (empty($c))
    {
        $out = array();
        @exec('command -v systemctl shutdown 2>/dev/null', $out);
        foreach ($out as $r)
        {
            $r = trim((string)$r);
            if ($r === '' || !is_executable($r))
                continue;
            $c[] = $r . (substr($r, -10) === 'systemctl' ? ' ' . $aSys : ' ' . $aShut);
        }
    }
    return $c;
}

function escapaComando($cmdline)
{
    $e = array();
    foreach (explode(' ', $cmdline) as $p)
        if ($p !== '')
            $e[] = escapeshellarg($p);
    return implode(' ', $e);
}

// Ritorna il primo candidato permesso via "sudo -n -l", o ''.
function scegliComandoAlimentazione($mode)
{
    foreach (candidatiAlimentazione($mode) as $cand)
    {
        $out = array();
        $rc = 1;
        @exec('sudo -n -l -- ' . escapaComando($cand) . ' >/dev/null 2>&1', $out, $rc);
        if ($rc !== 0)
            @exec('sudo -n ' . escapaComando(strtok($cand, " ")) . ' --help >/dev/null 2>&1', $out, $rc);
        if ($rc === 0)
            return $cand;
    }
    return '';
}

// Riscontro spegnimento: marker con timestamp, al reload eta marker vs uptime (/proc/uptime).
// Uptime < eta = RIUSCITO; oltre 180s = FALLITO; entro = IN ATTESA. Marker una tantum.
function markerPowerFile()
{
    return salsiccia_storage_path('power_attempt.json');
}

function uptimeMacchina()
{
    $u = @file_get_contents('/proc/uptime');
    if ($u === false)
        return null;
    $p = (float)strtok(trim((string)$u), " ");
    return $p > 0 ? $p : null;
}

// Pura e testabile: ritorna 'riuscito', 'fallito' o 'attesa'.
function esitoTentativoPower($markerTime, $now, $uptime)
{
    $soglia = 180;
    $eta = $now - $markerTime;
    if ($eta < 0)
        return 'attesa';
    if ($uptime !== null && $uptime < $eta)
        return 'riuscito';
    if ($eta > $soglia)
        return 'fallito';
    return 'attesa';
}

function scriviMarkerPower($mode)
{
    @file_put_contents(markerPowerFile(), json_encode(array('t' => time(), 'mode' => $mode)));
}

// F2.2 #69: braccio operativo power (da salsicciaStagisti 57d8298/master).
// RIAVVIA/SPEGNI: coda stampa mai lasciata a meta.
// DIRETTA: cancel best-effort pre-shutdown mai bloccante. RETE: invio sincrono, nessun job.
function svuotaCodaStampaPreSpegnimento()
{
    if (PRINTER_CONNECTION !== 'DIRETTA')
        return;
    if (!preg_match('/^[A-Za-z0-9_-]+$/', PRINTER_NAME))
    {
        cassa_log('error', 'power: cancel bloccato, PRINTER_NAME non whitelistato');
        return;
    }
    $cmd = 'cancel -a ' . escapeshellarg(printer_cups_queue(PRINTER_NAME)) . ' >/dev/null 2>&1';
    system($cmd, $rc);
    if ($rc !== 0)
        cassa_log('error', 'power: cancel coda fallito rc=' . $rc . ' printer=' . PRINTER_NAME);
}

// Schedula reboot/poweroff in background (sleep 2 cosi la risposta HTTP
// viene flushed prima che systemd killi PHP).
function schedulaAzioneAlimentazione($mode)
{
    $cmdline = scegliComandoAlimentazione($mode);
    if ($cmdline === '')
    {
        cassa_log('error', 'power: schedulazione ' . $mode . ' impossibile, nessun comando permesso');
        return false;
    }
    svuotaCodaStampaPreSpegnimento();
    $cmd = '(sleep 2; sudo ' . escapaComando($cmdline) . ') >/dev/null 2>&1 &';
    system($cmd, $rc);
    if ($rc !== 0)
    {
        cassa_log('error', 'power: schedulazione ' . $mode . ' fallita rc=' . $rc);
        return false;
    }
    scriviMarkerPower($mode);
    return true;
}

// F2.3 #70: schermate RIAVVIA/SPEGNI + riscontro + diagnostica (da salsicciaStagisti d57a201/57d8298).
// Riscontro: marker una tantum, solo echo. Diagnostica: firma CON $mode (fix bug upstream
// $mode indefinito che mostrava sempre i candidati reboot). Prerequisiti kiosk restano
// testo schermata come nel source: NOPASSWD sui comandi esatti + systemd presente.
function mostraRiscontroPower($mode)
{
    $f = markerPowerFile();
    $raw = is_readable($f) ? @file_get_contents($f) : false;
    if ($raw === false || trim((string)$raw) === '')
        return;
    $d = json_decode((string)$raw, true);
    if (!is_array($d) || empty($d['t']) || !isset($d['mode']) || $d['mode'] !== $mode)
    {
        if (is_array($d))
            return;
        @unlink($f);
        return;
    }
    $esito = esitoTentativoPower((int)$d['t'], time(), uptimeMacchina());
    $quando = date('d/m H:i:s', (int)$d['t']);
    if ($esito === 'riuscito')
    {
        echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;padding:12px;border-radius:6px;">COMANDO RIUSCITO: cassa riavviata/riaccesa dopo invio delle ' . $quando . '.</p>';
        @unlink($f);
    }
    elseif ($esito === 'fallito')
    {
        echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">COMANDO FALLITO: invio delle ' . $quando . ' senza effetto, cassa mai spenta. Verifica sudoers/systemd (vedi DIAGNOSTICA sotto).</p>';
        @unlink($f);
    }
    else
    {
        echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">COMANDO IN ATTESA: invio delle ' . $quando . ', la cassa dovrebbe spegnersi a momenti. Ricarica tra un minuto per il riscontro.</p>';
    }
}

// Diagnostica kiosk: valori reali letti al momento, comandi fissi.
function mostraDiagnosticaPower($mode)
{
    $who = array();
    @exec('whoami 2>/dev/null', $who);
    $utente = !empty($who[0]) ? trim((string)$who[0]) : '?';
    $cmd = scegliComandoAlimentazione($mode);
    $up = uptimeMacchina();
    echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">DIAGNOSTICA: utente=' . htmlspecialchars($utente, ENT_QUOTES, 'UTF-8') . ' os=' . PHP_OS_FAMILY . ' cmd=' . ($cmd !== '' ? htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8') : 'NESSUNO (permessi?)') . ' systemd=' . (is_dir('/run/systemd/system') ? 'si' : 'no') . ' uptime=' . ($up !== null ? (int)$up . 's' : 'n/d') . '</p>';
}

function mostraPower($mode, $action, $field, $titolo, $msgConferma, $labelBtn, $msgOk)
{
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }
    echo '<section class="admin-panel">';
    echo '<h3 class="admin-group-title admin-msg-big">' . $titolo . '</h3>';
    mostraRiscontroPower($mode);
    if (isset($_POST[$field]))
    {
        if (!csrf_ok())
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
        }
        else
        {
            $ok = schedulaAzioneAlimentazione($mode);
            if ($ok)
                echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;padding:12px;border-radius:6px;">' . $msgOk . '</p>';
            else
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">COMANDO NON AVVIATO: verifica sudoers/systemd sul kiosk.</p>';
            echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">Coda stampa: DIRETTA cancellata (best-effort), RETE nessun job pendente (FTP sincrono verso ' . htmlspecialchars(PRINTER_IP, ENT_QUOTES, 'UTF-8') . '). Se nulla accade: utente web senza NOPASSWD su /sbin/shutdown o host senza systemd.</p>';
        }
        echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
    }
    else
    {
        echo '<p class="admin-msg-big" style="color:#e54b3c;">ATTENZIONE: ' . $msgConferma . '</p>';
        echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">Coda stampa: DIRETTA verra svuotata, RETE nessun job pendente (invio FTP sincrono).</p>';
        echo '<form method="post" action="?action=' . $action . '" onsubmit="return confirm(\'Confermi?\');">';
        csrf_field();
        echo '<div class="admin-btn-row">';
        echo '<a href="index.php?action=c&ok=1" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
        echo '<button type="submit" name="' . $field . '" value="1" class="opzione-btn danger">' . $labelBtn . '</button>';
        echo '</div>';
        echo '</form>';
    }
    mostraDiagnosticaPower($mode);
    echo '</section>';
}

function mostraRestart()
{
    mostraPower('reboot', 'restart', 'restart', 'RIAVVIA TERMINALE', 'il terminale cassa verra riavviato.', 'RIAVVIA ORA', 'RIAVVIO SCHEDULATO: il terminale si riavvia tra pochi secondi.');
}

function mostraShutdown()
{
    mostraPower('poweroff', 'shutdown', 'shutdown', 'SPEGNI TERMINALE', 'il terminale cassa verra spento.', 'SPEGNI ORA', 'SPEGNIMENTO SCHEDULATO: il terminale si spegne tra pochi secondi.');
}

// Schermata unica CONTATORI: tutti i venduti monitorati con nome + quantita
// (un solo GROUP BY via contatori_totali, paginato in PHP come le viste stat).
function mostraContatori($mysqli)
{
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }
    echo '<section class="admin-panel">';
    echo '<h3 class="admin-group-title">CONTATORI - PRODOTTI VENDUTI MONITORATI</h3>';
    $rpp = 5;
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($pagina < 1)
        $pagina = 1;
    $righe = contatori_totali($mysqli);
    $tot = count($righe);
    $totPagine = max(1, (int)ceil($tot / $rpp));
    if ($pagina > $totPagine)
        $pagina = $totPagine;
    $offset = ($pagina - 1) * $rpp;
    if ($tot == 0)
    {
        echo '<p style="font-weight:800;text-align:center;color:#8c9ba5;">NESSUN PRODOTTO MONITORATO</p>';
    }
    else
    {
        echo '<table class="modifica-table">';
        echo '<thead><tr><th class="intestazione-tabella-descrizione">NOME PRODOTTO</th><th class="intestazione-tabella-quantita">QUANTITA VENDUTI</th></tr></thead>';
        echo '<tbody>';
        foreach (array_slice($righe, $offset, $rpp) as $c)
        {
            echo '<tr><td class="cella-tabella-descrizione">' . htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8') . '</td><td class="cella-tabella-quantita">' . ($c['totale'] + 0) . '</td></tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '<div class="admin-btn-row">';
        if ($pagina > 1)
            echo '<a href="?action=contatori&page=' . ($pagina - 1) . '" class="opzione-btn" style="padding:12px 20px;">&lt;</a>';
        foreach (array_unique(array(1, $pagina - 1, $pagina, $pagina + 1, $totPagine)) as $numPagina)
        {
            if ($numPagina < 1 || $numPagina > $totPagine)
                continue;
            if ($numPagina == $pagina)
                echo '<span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;">' . $numPagina . '</span>';
            else
                echo '<a href="?action=contatori&page=' . $numPagina . '" class="opzione-btn" style="padding:12px 20px;">' . $numPagina . '</a>';
        }
        if ($pagina < $totPagine)
            echo '<a href="?action=contatori&page=' . ($pagina + 1) . '" class="opzione-btn" style="padding:12px 20px;">&gt;</a>';
        echo '</div>';
    }
    echo '<div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
    echo '</section>';
}

// Repair database: REPAIR TABLE su tutte le tabelle
// (port di 1.0/ripristino.php dal branch modificaStampa, riusa $mysqli)
function mostraRipristinaDb($mysqli)
{
    // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }

    echo '<section class="admin-panel">';
    echo '<h3 class="admin-group-title">RIPRISTINA DATABASE</h3>';
    if (isset($_POST['ripristina']))
    {
        if (!csrf_ok())
        {
            echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
        else
        {
        $ris = $mysqli->query("SHOW TABLES");
        while ($row = $ris->fetch_array())
        {
            $table = $row[0];
            if (!preg_match('/^[A-Za-z0-9_]+$/', $table))
            {
                cassa_log('warning', "REPAIR bloccato: tabella non whitelistata");
                continue;
            }
            if ($mysqli->query("REPAIR TABLE `$table`"))
                echo "Tabella <b>$table</b> riparata.<br>";
            else
                echo "Errore su <b>$table</b>: " . htmlspecialchars($mysqli->error) . "<br>";
        }
        echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
    }
    else
    {
        echo '<p style="color:#e54b3c;font-weight:800;text-align:center;">ATTENZIONE: verr&agrave; eseguito il recupero di tutte le tabelle.</p>';
        echo '<form method="post" action="?action=repair" onsubmit="return confirm(\'Confermi il recupero di tutte le tabelle?\');">';
        csrf_field();
        echo '<div class="admin-btn-row">';
        echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
        echo '<button type="submit" name="ripristina" value="1" class="opzione-btn">REPAIR DATABASE</button>';
        echo '</div>';
        echo '</form>';
    }
    echo '</section>';
}

// Modifica info festa: legge/scrive EVENT_NAME e DURATA_FESTA in storage/festa.json
// via festa_leggi()/festa_imposta() (F0.3, pattern T17); set.inc resta congelato
// read-only, mai rewrite. (port di 1.0/configurazione.php dal branch modificaStampa)
function mostraModificaInfo()
{
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }
    $letto = festa_leggi();
    $eventName = isset($letto['event_name']) ? (string)$letto['event_name'] : '';
    $durataFesta = isset($letto['durata_festa']) ? (string)$letto['durata_festa'] : '1';

    $messaggio = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if (!csrf_ok())
        {
            $messaggio = 'TOKEN NON VALIDO.';
        }
        else
        {
        festa_imposta(isset($_POST['EVENT_NAME']) ? $_POST['EVENT_NAME'] : '', isset($_POST['DURATA_FESTA']) ? $_POST['DURATA_FESTA'] : '');
        $letto = festa_leggi();
        $eventName = isset($letto['event_name']) ? (string)$letto['event_name'] : '';
        $durataFesta = isset($letto['durata_festa']) ? (string)$letto['durata_festa'] : '1';
        $messaggio = 'Configurazione salvata con successo.';
        }
    }

    echo '<section class="admin-panel" style="align-items:center;text-align:center;">';
    echo '<h3 class="admin-group-title">CONFIGURAZIONE FESTA</h3>';
    if ($messaggio != '')
        echo '<p style="background:#5cb85c;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">' . $messaggio . '</p>';
    echo '<form method="post" action="?action=info" style="display:flex;flex-direction:column;gap:16px;max-width:560px;width:100%;margin:0 auto;align-items:center;text-align:center;">';
    csrf_field();
    echo '<label style="font-weight:800;display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;width:100%;"><span style="font-size:18px;letter-spacing:1px;color:#2b3d4e;text-transform:uppercase;">NOME FESTA (max 32)</span><input type="text" name="EVENT_NAME" value="' . htmlspecialchars($eventName) . '" maxlength="32" class="codice-text-field"></label>';
    echo '<label style="font-weight:800;display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;width:100%;"><span style="font-size:18px;letter-spacing:1px;color:#2b3d4e;text-transform:uppercase;">DURATA (GIORNI)</span><input type="number" name="DURATA_FESTA" value="' . htmlspecialchars($durataFesta) . '" min="1" class="codice-text-field"></label>';
    echo '<div class="admin-btn-row">';
    echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
    echo '<button type="submit" class="opzione-btn">SALVA MODIFICHE</button>';
    echo '</div>';
    echo '</form>';
    echo '</section>';
}

// CAMBIA CARTA STAMPA (F3.4 #64, toggle continua <-> singoli): riusa F3.1
// (cartaStampaLeggiSetup) + F3.2 (PrintService::inviaSetupCarta) + F3.3
// (impostaCartaStampa). Setup/invio falliti => errore, modalita invariata.
// Pattern mostraRipristinaDb: isAdmin + POST + CSRF + confirm.
function mostraPrintReset()
{
    // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }
    $modo = (defined('CONTINUOUS_LABEL') && CONTINUOUS_LABEL) ? 'CONTINUA' : 'SINGOLI';
    $nuovo = (defined('CONTINUOUS_LABEL') && CONTINUOUS_LABEL) ? '0' : '1';
    $modoNuovo = $nuovo === '1' ? 'CONTINUA' : 'SINGOLI';
    echo '<section class="admin-panel">';
    echo '<h3 class="admin-group-title admin-msg-big">CAMBIA CARTA STAMPA - CARTA ATTUALE: ' . $modo . '</h3>';
    if (isset($_POST['cambia_carta']))
    {
        if (!csrf_ok())
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
        else
        {
        $continuaNuova = ($nuovo === '1');
        $erroreSetup = '';
        $setup = cartaStampaLeggiSetup(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE, $continuaNuova, $erroreSetup, salsiccia_storage_path('printerCommand'));
        if ($setup === false)
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">' . htmlspecialchars($erroreSetup, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        elseif (!\Salsiccia\Cassa\PrintService::inviaSetupCarta($setup))
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">INVIO SETUP CARTA ALLA STAMPANTE FALLITO, modalita invariata.</p>';
        }
        elseif (impostaCartaStampa($nuovo))
        {
            echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">Carta commutata a ' . $modoNuovo . ': setup stampante applicato, la prossima stampa usa la nuova modalita.</p>';
        }
        else
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">SETUP STAMPANTE APPLICATO MA SCRITTURA STATO CARTA FALLITA: riallineare al prossimo toggle.</p>';
        }
        echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
    }
    else
    {
        echo '<p class="admin-msg-big" style="color:#e54b3c;">ATTENZIONE: dopo aver cambiato fisicamente la carta, la modalita di stampa passera da ' . $modo . ' a ' . $modoNuovo . '.</p>';
        echo '<form method="post" action="?action=print_reset" onsubmit="return confirm(\'Cambiare carta di stampa in ' . $modoNuovo . '?\');">';
        csrf_field();
        echo '<div class="admin-btn-row">';
        echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
        echo '<button type="submit" name="cambia_carta" value="1" class="opzione-btn">CAMBIA IN ' . $modoNuovo . '</button>';
        echo '</div>';
        echo '</form>';
    }
    echo '</section>';
}

// Selezione stampante (F4.5 #55, stato finale stagisti HEAD): valida contro
// gate printer_gate_allowed() (F4.1), salva in storage/stampante_selezione.json
// via printer_selection_file() (letta a boot in set.inc F4.4). Solo
// "DIRETTA"/"RETE", "ZPL"/"EPL", LOCK_EX. IP opzionale, solo RETE.
function impostaStampanteSelezionata($name, $conn, $lang, $ip = '')
{
    $name = (string)$name;
    $conn = (string)$conn;
    $lang = (string)$lang;
    $ip = trim((string)$ip);
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $name))
        return false;
    if (!printer_gate_allowed($name, $conn, $lang))
        return false;
    if ($ip !== '' && ($conn !== 'RETE' || filter_var($ip, FILTER_VALIDATE_IP) === false))
        return false;
    $d = array('name' => $name, 'connection' => $conn, 'language' => $lang, 'ip' => $ip);
    if (file_put_contents(printer_selection_file(), json_encode($d), LOCK_EX) === false)
    {
        cassa_log('error', "stampante_selezione: scrittura fallita");
        return false;
    }
    return true;
}

// Raggiungibilita' veritiera per la pagina switch (F4.5 #55): DIRETTA da stato
// CUPS reale printer_lpstat_stato() + presenza USB lpinfo (F4.2) via
// printer_stato_locale_ok(); RETE ping-only via printer_is_reachable().
// Senza CUPS: rosso "non verificabile", mai fatal.
function stampante_switch_ok($nome, $conn, $ip = '')
{
    if ($conn === 'RETE')
        return printer_is_reachable($nome, $conn, $ip);
    $st = printer_lpstat_stato($nome);
    $dev = isset($st['device']) ? (string)$st['device'] : '';
    $usb = stripos($dev, 'usb://') === 0 ? printer_usb_locale_presente($dev) : true;
    return printer_stato_locale_ok($st, $usb);
}

// CAMBIA STAMPANTE (F4.5 #55, stato finale stagisti HEAD 0632388): una card
// per stampante con pallino verde/rosso (stampante_switch_ok sopra), grid 2x2
// ($rpp=4), raggiungibili prime, senza riga STATO (272aee3). Solo la GX420t
// e' dual: scelta ZPL/EPL via pill, SELEZIONA disabilitato finche' non scegli;
// le altre mostrano badge fisso ZPL/EPL + SELEZIONA diretto. Selezione
// irraggiungibile bloccata (tranne RETE ping-only).
// Pattern mostraPrintReset: isAdmin + POST + CSRF + confirm + impostaStampanteSelezionata().
function mostraSwitchPrinter()
{
    // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }
    echo '<section class="admin-panel switch-printer-panel">';
    echo '<h3 class="admin-group-title admin-msg-big">CAMBIA STAMPANTE - ATTUALE: ' . htmlspecialchars(PRINTER_NAME . ' - ' . PRINTER_CONNECTION . ' - ' . PRINTER_LANGUAGE, ENT_QUOTES, 'UTF-8') . '</h3>';
    if (isset($_POST['cambia_stampante']))
    {
        if (!csrf_ok())
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
        }
        else
        {
            $nome = (string)($_POST['nome'] ?? '');
            $conn = (string)($_POST['conn'] ?? '');
            $lingua = (string)($_POST['lingua'] ?? '');
            $ip = $conn === 'RETE' ? trim((string)($_POST['ip'] ?? '')) : '';
            // riuso reachability F4.2; ip POST o fallback PRINTER_IP come in lista
            $ipCheck = $conn === 'RETE' ? ($ip !== '' ? $ip : (defined('PRINTER_IP') ? PRINTER_IP : '')) : '';
            if (!stampante_switch_ok($nome, $conn, $ipCheck))
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">STAMPANTE NON RAGGIUNGIBILE (pallino rosso), selezione non cambiata.</p>';
            }
            elseif (impostaStampanteSelezionata($nome, $conn, $lingua, $ip))
            {
                echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;padding:12px;border-radius:6px;">Stampante commutata a ' . htmlspecialchars($nome . ' - ' . $conn . ' - ' . $lingua, ENT_QUOTES, 'UTF-8') . ': la prossima stampa usa la nuova selezione.</p>';
            }
            else
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">SELEZIONE NON VALIDA (gate), stampante invariata.</p>';
            }
        }
        echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
    }
    else
    {
        echo '<script>function selModo(r){var f=r.form,b=f.cambia_stampante;if(b){if(b.getAttribute("data-unreach")==="1")return;b.disabled=false;b.style.opacity="";b.style.cursor="";}var L=f.querySelectorAll("label.opzione-btn");for(var i=0;i<L.length;i++){L[i].removeAttribute("style");}var l=r.parentNode;l.style.border="2px solid #2b3d4e";l.style.background="#2b3d4e";l.style.color="#fff";}</script>';
        $raggiungibili = array();
        $altre = array();
        foreach (printer_known_printers() as $p)
        {
            $p['ok'] = stampante_switch_ok((string)$p['name'], (string)$p['connection'], defined('PRINTER_IP') ? PRINTER_IP : '');
            if ($p['ok'])
                $raggiungibili[] = $p;
            else
                $altre[] = $p;
        }
        $ordinate = array_merge($raggiungibili, $altre);
        $rpp = 4;
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pagina < 1)
            $pagina = 1;
        $totPagine = max(1, (int)ceil(count($ordinate) / $rpp));
        if ($pagina > $totPagine)
            $pagina = $totPagine;
        $visibili = array_slice($ordinate, ($pagina - 1) * $rpp, $rpp);
        echo '<div class="switch-printer-grid">';
        foreach ($visibili as $p)
        {
            $nome = (string)$p['name'];
            $conn = (string)$p['connection'];
            $ok = !empty($p['ok']);
            $attuale = ($nome === PRINTER_NAME && $conn === PRINTER_CONNECTION);
            echo '<div class="switch-printer-card">';
            echo '<p class="admin-msg-big" style="color:#2b3d4e;margin:0 0 4px;"><span title="' . ($ok ? 'Raggiungibile' : 'Non raggiungibile') . '" style="color:' . ($ok ? '#5cb85c' : '#d9534f') . ';">&#9679;</span> ' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ($attuale ? ' <span style="display:inline-flex;align-items:center;background:#5cb85c;color:#fff;font-weight:800;border-radius:12px;padding:2px 12px;font-size:14px;">ATTUALE</span>' : '') . '</p>';
            echo '<p class="admin-msg-big" style="color:#8c9ba5;margin:0 0 8px;">CONNESSIONE: ' . htmlspecialchars($conn, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<div class="admin-btn-row" style="align-items:center;">';
            if ($nome === 'GX420t')
            {
                echo '<span class="admin-msg-big" style="color:#8c9ba5;">MODALITA STAMPA:</span>';
                echo '<form method="post" action="?action=switch_printer" onsubmit="return confirm(\'Cambiare stampante?\');" style="display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:center;">';
                csrf_field();
                echo '<input type="hidden" name="nome" value="GX420t">';
                echo '<input type="hidden" name="conn" value="DIRETTA">';
                foreach (array('ZPL', 'EPL') as $lingua)
                {
                    $sel = ($attuale && PRINTER_LANGUAGE === $lingua);
                    echo '<label class="opzione-btn"' . ($sel ? ' style="border:2px solid #2b3d4e;background:#2b3d4e;color:#fff;"' : '') . '><input type="radio" name="lingua" value="' . $lingua . '"' . ($sel ? ' checked' : '') . ($ok ? '' : ' disabled') . ' onchange="selModo(this)" style="display:none;"> ' . $lingua . '</label>';
                }
                echo '<button type="submit" name="cambia_stampante" value="1" class="opzione-btn"' . ((!$ok || !$attuale) ? ' disabled style="opacity:.45;cursor:not-allowed;"' : '') . (!$ok ? ' data-unreach="1"' : '') . '>SELEZIONA</button>';
                echo '</form>';
            }
            else
            {
                $lingua = (string)$p['language'];
                echo '<span class="admin-msg-big" style="color:#8c9ba5;">MODALITA STAMPA:</span>';
                echo '<span class="opzione-btn" style="border:2px solid #2b3d4e;cursor:default;">' . htmlspecialchars($lingua, ENT_QUOTES, 'UTF-8') . '</span>';
                echo '<form method="post" action="?action=switch_printer" onsubmit="return confirm(\'Cambiare stampante?\');" style="display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:center;">';
                csrf_field();
                echo '<input type="hidden" name="nome" value="' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . '">';
                echo '<input type="hidden" name="conn" value="' . htmlspecialchars($conn, ENT_QUOTES, 'UTF-8') . '">';
                echo '<input type="hidden" name="lingua" value="' . htmlspecialchars($lingua, ENT_QUOTES, 'UTF-8') . '">';
                if ($conn === 'RETE')
                    echo '<input type="hidden" name="ip" value="' . htmlspecialchars(defined('PRINTER_IP') ? PRINTER_IP : '', ENT_QUOTES, 'UTF-8') . '">';
                echo '<button type="submit" name="cambia_stampante" value="1" class="opzione-btn"' . ($ok ? '' : ' disabled style="opacity:.45;cursor:not-allowed;"') . '>SELEZIONA</button>';
                echo '</form>';
            }
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '<div class="admin-btn-row">';
        if ($pagina > 1)
            echo '<a href="?action=switch_printer&page=' . ($pagina - 1) . '" class="opzione-btn" style="padding:12px 20px;">&lt;</a>';
        foreach (array_unique(array(1, $pagina - 1, $pagina, $pagina + 1, $totPagine)) as $numPagina)
        {
            if ($numPagina < 1 || $numPagina > $totPagine)
                continue;
            if ($numPagina == $pagina)
                echo '<span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;">' . $numPagina . '</span>';
            else
                echo '<a href="?action=switch_printer&page=' . $numPagina . '" class="opzione-btn" style="padding:12px 20px;">' . $numPagina . '</a>';
        }
        if ($pagina < $totPagine)
            echo '<a href="?action=switch_printer&page=' . ($pagina + 1) . '" class="opzione-btn" style="padding:12px 20px;">&gt;</a>';
        echo '</div>';
        echo '<div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
    }
    echo '</section>';
}

// Layout resto condiviso tra esito stampa ed errore stampante (#102): stesse
// classi cassa (stampa-header, banconote-grid, monete-grid, metodo-grid,
// stampa-footer). Solo echo da scalari: nessuna scrittura DB e nessuna
// emissione fiscale qui, il fiscale resta nel solo ramo stampato sotto.
function mostraLayoutResto($totale, $pagato, $metodo, $base_url, $mostra_nuovo = true)
{
    $totale = (float)$totale;
    $pagato = (float)$pagato;
    // T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato).
    $pm = \Salsiccia\Cassa\PayMethod::tryFrom((string)$metodo);
    $metodo = $pm ? $pm->value : '';
    $resto = max(0, $pagato - $totale);

    $totale_fmt = number_format($totale, 2, ',', '.');
    $pagato_fmt = number_format($pagato, 2, ',', '.');
    $resto_fmt = number_format($resto, 2, ',', '.');

    echo '<header class="stampa-header">';
    echo '<h1>TOTALE ORDINE: &euro; ' . $totale_fmt . '</h1>';
    echo '</header>';

    echo '<section class="stampa-section">';

    //Banconote
    echo '<p class="gruppo-titolo"><span class="gruppo-titolo-icona">💶</span> BANCONOTE</p>';
    echo '<div class="banconote-grid">';
    echo '<div class="banconote-riga banconote-riga-1">';
    $banconote_1 = array(5, 10, 20, 50);
    foreach ($banconote_1 as $val)
    {
        $nuovo_pagato = $pagato + $val;
        $img_file = 'asset/soldi/' . $val . '_euro.png';
        echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $nuovo_pagato . '&metodo=' . $metodo . '\'" class="banconota-btn" style="background-image:url(\'' . $img_file . '\')">';
        echo '<span class="valore">' . $val . '€</span></button>';
    }
    echo '</div>';
    echo '<div class="banconote-riga banconote-riga-2">';
    $banconote_2 = array(100, 200, 500);
    foreach ($banconote_2 as $val)
    {
        $nuovo_pagato = $pagato + $val;
        $img_file = 'asset/soldi/' . $val . '_euro.png';
        echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $nuovo_pagato . '&metodo=' . $metodo . '\'" class="banconota-btn" style="background-image:url(\'' . $img_file . '\')">';
        echo '<span class="valore">' . $val . '€</span></button>';
    }
    echo '</div>';
    echo '</div>';

    //monete
    echo '<p class="gruppo-titolo"><span class="gruppo-titolo-icona">🪙</span> MONETE</p>';
    echo '<div class="monete-grid">';
    $monete = array(2, 1, 0.50, 0.20, 0.10, 0.05, 0.01);
    foreach ($monete as $val)
    {
        $nuovo_pagato = $pagato + $val;
        $label = $val >= 1 ? $val . '€' : ($val * 100) . 'c';
        $img_file = 'asset/soldi/' . ($val >= 1 ? $val : ($val * 100) . 'c') . '_euro.png';
        $classe_rame = ($val <= 0.05) ? ' moneta-rame' : '';
        echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $nuovo_pagato . '&metodo=' . $metodo . '\'" class="moneta-btn' . $classe_rame . '" style="background-image:url(\'' . $img_file . '\')">';
        echo '<span class="valore">' . $label . '</span></button>';
    }
    echo '</div>';

    //metodo pagamento (T26: valori da PayMethod enum, unica fonte)
    echo '<p class="metodo-titolo">STAMPA SCONTRINO FISCALE, METODO DI PAGAMENTO:</p>';
    echo '<div class="metodo-grid">';
    $attivo_contanti = ($metodo == \Salsiccia\Cassa\PayMethod::Contanti->value) ? ' attivo' : '';
    $attivo_carta = ($metodo == \Salsiccia\Cassa\PayMethod::Carta->value) ? ' attivo' : '';
    echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $pagato . '&metodo=' . \Salsiccia\Cassa\PayMethod::Contanti->value . '\'" class="metodo-btn' . $attivo_contanti . '">';
    echo '<span class="metodo-btn-icona">💵</span>';
    echo '<span class="metodo-btn-label">CONTANTI</span></button>';
    echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $pagato . '&metodo=' . \Salsiccia\Cassa\PayMethod::Carta->value . '\'" class="metodo-btn' . $attivo_carta . '">';
    echo '<span class="metodo-btn-icona">💳</span>';
    echo '<span class="metodo-btn-label">CARTA DI CREDITO</span></button>';
    echo '</div>';

    if ($mostra_nuovo)
        echo '<div style="text-align:center;"><button onclick="window.location.href=\'index.php\'" class="opzione-btn" style="padding:16px 40px;display:inline-flex;">NUOVO ORDINE</button></div>';

    //Footer PAGATO-TOTALE-RESTO
    echo '<div class="stampa-footer">';
    echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=0\'" class="stampa-close-btn">✕</button>';
    echo '<div class="stampa-footer-campo">';
    echo '<label>PAGATO</label>';
    echo '<input type="text" value="€ ' . $pagato_fmt . '" readonly>';
    echo '</div>';
    echo '<div class="stampa-footer-campo">';
    echo '<label>TOTALE</label>';
    echo '<input type="text" value="€ ' . $totale_fmt . '" readonly>';
    echo '</div>';
    echo '<div class="stampa-footer-campo">';
    echo '<label>RESTO</label>';
    echo '<input type="text" value="€ ' . $resto_fmt . '" readonly>';
    echo '</div>';
    echo '</div>';

    echo '</section>';
}

// Stampa
function mostraSchermataStampa($mysqli)
{
    $id_cassa = cassaCorrente();

    // Errore stampante (#102): ordine ancora aperto (chiuso=0), stesso layout
    // resto via mostraLayoutResto. RIPROVA rilancia la stampa una sola volta;
    // i click su banconote/monete restano qui e non ristampano mai.
    if (isset($_GET['stampa_err']))
    {
        $id_ordine_err = (int)$_GET['stampa_err'];
        $totale_err = 0;
        $risErr = db_select($mysqli, "SELECT totale FROM ordini WHERE id_ordine = ? AND id_cassa = ? AND chiuso = '0'", 'ii', array($id_ordine_err, $id_cassa));
        if ($risErr && ($rigaErr = mysqli_fetch_array($risErr)))
            $totale_err = (float)$rigaErr['totale'];
        else
        {
            echo '<div style="text-align:center;margin-top:60px;">';
            echo '<p style="font-size:24px;font-weight:800;color:#8c9ba5;margin-bottom:40px;">NESSUN ORDINE DA RIPROVARE</p>';
            echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:20px 40px;display:inline-block;">TORNA</a>';
            echo '</div>';
            return;
        }
        $pagato_err = isset($_GET['pagato']) ? (float)$_GET['pagato'] : 0;
        // T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato).
        $pm_err = isset($_GET['metodo']) ? \Salsiccia\Cassa\PayMethod::tryFrom((string)$_GET['metodo']) : null;
        $metodo_err = $pm_err ? $pm_err->value : '';
        $base_err = '?action=s&stampa_err=' . $id_ordine_err;
        echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:6px;border-radius:6px;">STAMPANTE NON RAGGIUNGIBILE, ordine NON stampato e NON chiuso: controlla la stampante e riprova.</p>';
        mostraLayoutResto($totale_err, $pagato_err, $metodo_err, $base_err, false);
        echo '<div style="text-align:center;margin-top:8px;">';
        echo '<a href="index.php?action=s" class="opzione-btn" style="text-decoration:none;padding:16px 40px;display:inline-block;">RIPROVA STAMPA</a> ';
        echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:16px 40px;display:inline-block;">TORNA</a> ';
        echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:16px 40px;display:inline-block;">NUOVO ORDINE</a>';
        echo '</div>';
        return;
    }

    // Stampa una sola volta all'ingresso (?action=s senza &stampato=), poi JS-redirect
    // con &stampato= per la UI resto: evita ristampe sui click di banconote/monete.
    if (!isset($_GET['stampato']))
    {
        $ris = db_select($mysqli, "SELECT COALESCE(NULLIF(TRIM(prodotti.testo_biglietto), ''), prodotti.descrizione_prod) AS testo, prodotti.olpp AS olpp, righe_ordini.quantita, righe_ordini.totale, ordini.totale as tot_ord, ordini.n_pezzi, ordini.id_ordine, ordini.tipo, prodotti.id_prodotto AS id_prodotto FROM prodotti, ordini, righe_ordini WHERE ordini.id_ordine = righe_ordini.id_ordine AND prodotti.id_prodotto = righe_ordini.id_prodotto AND ordini.chiuso = '0' AND ordini.id_cassa = ?", 'i', array($id_cassa));
        $numero_righe = $ris ? mysqli_num_rows($ris) : 0;

        if ($numero_righe == 0)
        {
            echo '<div style="text-align:center;margin-top:60px;">';
            echo '<p style="font-size:24px;font-weight:800;color:#8c9ba5;margin-bottom:40px;">NESSUN BIGLIETTO DA STAMPARE</p>';
            echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:20px 40px;display:inline-block;">NUOVO ORDINE</a>';
            echo '</div>';
            return;
        }

        $ret_val = \Salsiccia\Cassa\PrintService::generaFileStampa($ris, $numero_righe);
        if ($ret_val === false)
            return; // errore configurazione stampante, messaggio gia' mostrato

        // Invio alla stampante fallito: l'ordine resta aperto (chiuso=0) cosi'
        // la cassa puo' riprovare; chiuderlo lo marcherebbe come stampato.
        // Redirect al ramo stampa_err: stesso layout resto, nessuna ristampa
        // sui click di banconote/monete (come il ramo stampato sotto).
        if (empty($ret_val['inviato']))
        {
            $qs = '?action=s&stampa_err=' . (int)$ret_val['id_ordine'];
            echo '<script>window.location.href=\'' . $qs . '\';</script>';
            return;
        }

        $id_ordine = (int)$ret_val['id_ordine'];
        $num_pezzi = (int)$ret_val['num_pezzi'];
        $tot_ordine = (float)$ret_val['totale'];
        db_exec($mysqli, "UPDATE ordini SET chiuso = 1, num_biglietti = ? WHERE id_ordine = ?", 'ii', array($num_pezzi, $id_ordine));
        $qs = '?action=s&stampato=' . $id_ordine . '&tot=' . $tot_ordine;
        echo '<script>window.location.href=\'' . $qs . '\';</script>';
        return;
    }

    $id_ordine = (int)$_GET['stampato'];
    // Totale sempre dal DB via stampato, mai dal GET (display resto non fidato).
    $totale = 0;
    $risTot = db_select($mysqli, "SELECT totale FROM ordini WHERE id_ordine = ?", 'i', array($id_ordine));
    if ($risTot && ($rigaTot = mysqli_fetch_array($risTot)))
        $totale = (float)$rigaTot['totale'];
    $pagato = isset($_GET['pagato']) ? (float)$_GET['pagato'] : 0;
    // T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato).
    $pm = isset($_GET['metodo']) ? \Salsiccia\Cassa\PayMethod::tryFrom((string)$_GET['metodo']) : null;
    $metodo = $pm ? $pm->value : '';
    $base_url = '?action=s&stampato=' . $id_ordine . '&tot=' . $totale;

    // Scontrino fiscale non bloccante (#92): al primo metodo scelto emette verso il
    // registratore o accoda; la schermata resto si mostra sempre e comunque.
    if ($metodo !== '')
    {
        try
        {
            $fisc = \Salsiccia\Fiscale\Fiscale::emettiScontrino($mysqli, $id_ordine, $metodo);
            if (empty($fisc['ok']) && !empty($fisc['fallback']))
                echo '<p style="background:#f0ad4e;color:#fff;font-weight:800;text-align:center;padding:10px;border-radius:6px;">REGISTRATORE NON RISPONDE: scontrino in coda, la vendita prosegue.</p>';
        }
        catch (Throwable $e)
        {
            cassa_log('warning', 'fiscale non bloccante: ' . $e->getMessage());
        }
    }

    mostraLayoutResto($totale, $pagato, $metodo, $base_url);
}
