<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successori parziali esistenti src/Cassa/OrderService.php + src/Catalog/* + src/Cassa/PrintService.php + src/Fiscale/Fiscale.php; resto pianificato in src/{Cassa,Printer,System,Config}/ (v. docs/ARCHITETTURA_REVISTA.md §6 e §9, fasi 1-3); rimozione solo a migrazione completa, rivalutare entro 2026-12-31.

// F3.2 #95: nessun side-effect a top-level, richiedibile senza DB/sessione.
// Init (require set/db/funzioni, sessione, $cat/$id_cassa/geometria, dispatch)
// vive in bootstrap.php + flusso F1 (CassaController/CassaView con parametri espliciti).

// F5.5b #118: questo file chiama ancora i globali dello shim env.inc (cassa_log,
// salsiccia_storage_path/login_*, printer_*, flag fiera/festa): e' il consumer
// legacy che #120 migra sui moduli F5, quindi richiede lo shim da solo, come
// faceva funzioni.inc prima di F4.4. Con la catena d'ingresso su F5, set.inc e
// dbConnect.php non lo caricano piu'; qui finisce il require. Sparisce con #121.
require_once __DIR__ . '/env.inc';

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
// F2.4 #91: unica implementazione vera in Salsiccia\Cassa\AdminView::tastierino()
// (src/Cassa/AdminView.php); qui resta solo lo shim con stessa firma.
// Delete legacy mai in F2 (§9).
function mostraPannelloConfig()
{
    \Salsiccia\Cassa\AdminView::tastierino();
}

//Pannello admin
// F2.4 #91: unica implementazione vera in Salsiccia\Cassa\AdminView::pannello()
// (src/Cassa/AdminView.php); qui resta solo lo shim con stessa firma.
// Delete legacy mai in F2 (§9).
function mostraPannelloAdmin()
{
    \Salsiccia\Cassa\AdminView::pannello();
}

// F2.1 #68: preflight e funzioni pure power (da salsicciaStagisti 6b796de+d57a201).
// F2.5 #92: unica implementazione vera in Salsiccia\System\Power
// (src/System/Power.php, corpi verbatim invariati); qui restano solo gli shim
// con stesse firme. Delete legacy mai in F2 (§9).
function candidatiAlimentazione($mode)
{
    return \Salsiccia\System\Power::candidatiAlimentazione($mode);
}

function escapaComando($cmdline)
{
    return \Salsiccia\System\Power::escapaComando($cmdline);
}

// Ritorna il primo candidato permesso via "sudo -n -l", o ''.
function scegliComandoAlimentazione($mode)
{
    return \Salsiccia\System\Power::scegliComandoAlimentazione($mode);
}

// Riscontro spegnimento: marker con timestamp, al reload eta marker vs uptime (/proc/uptime).
// Uptime < eta = RIUSCITO; oltre 180s = FALLITO; entro = IN ATTESA. Marker una tantum.
function markerPowerFile()
{
    return \Salsiccia\System\Power::markerPowerFile();
}

function uptimeMacchina()
{
    return \Salsiccia\System\Power::uptimeMacchina();
}

// Pura e testabile: ritorna 'riuscito', 'fallito' o 'attesa'.
function esitoTentativoPower($markerTime, $now, $uptime)
{
    return \Salsiccia\System\Power::esitoTentativoPower($markerTime, $now, $uptime);
}

function scriviMarkerPower($mode)
{
    \Salsiccia\System\Power::scriviMarkerPower($mode);
}

// F2.2 #69: braccio operativo power (da salsicciaStagisti 57d8298/master).
// RIAVVIA/SPEGNI: coda stampa mai lasciata a meta.
// DIRETTA: cancel best-effort pre-shutdown mai bloccante. RETE: invio sincrono, nessun job.
function svuotaCodaStampaPreSpegnimento()
{
    \Salsiccia\System\Power::svuotaCodaStampaPreSpegnimento();
}

// Schedula reboot/poweroff in background (sleep 2 cosi la risposta HTTP
// viene flushed prima che systemd killi PHP).
function schedulaAzioneAlimentazione($mode)
{
    return \Salsiccia\System\Power::schedulaAzioneAlimentazione($mode);
}

// F2.3 #70: schermate RIAVVIA/SPEGNI + riscontro + diagnostica (da salsicciaStagisti d57a201/57d8298).
// F2.5 #92: unica implementazione vera in Salsiccia\Cassa\AdminView::
// riscontroPower()/diagnosticaPower()/power()/restart()/shutdown()
// (src/Cassa/AdminView.php, logica via System\Power); qui restano solo gli
// shim con stesse firme. Delete legacy mai in F2 (§9).
function mostraRiscontroPower($mode)
{
    \Salsiccia\Cassa\AdminView::riscontroPower($mode);
}

// Diagnostica kiosk: valori reali letti al momento, comandi fissi.
function mostraDiagnosticaPower($mode)
{
    \Salsiccia\Cassa\AdminView::diagnosticaPower($mode);
}

function mostraPower($mode, $action, $field, $titolo, $msgConferma, $labelBtn, $msgOk)
{
    \Salsiccia\Cassa\AdminView::power($mode, $action, $field, $titolo, $msgConferma, $labelBtn, $msgOk);
}

function mostraRestart()
{
    \Salsiccia\Cassa\AdminView::restart();
}

function mostraShutdown()
{
    \Salsiccia\Cassa\AdminView::shutdown();
}

// Schermata unica CONTATORI: tutti i venduti monitorati con nome + quantita
// (un solo GROUP BY via contatori_totali, paginato in PHP come le viste stat).
// F2.4 #91: unica implementazione vera in Salsiccia\Cassa\AdminView::contatori()
// (src/Cassa/AdminView.php, dati via contatori_totali() as-is, lo sposta F4);
// qui resta solo lo shim con stessa firma. Delete legacy mai in F2 (§9).
function mostraContatori($mysqli)
{
    \Salsiccia\Cassa\AdminView::contatori($mysqli);
}

// Repair database: REPAIR TABLE su tutte le tabelle
// (port di 1.0/ripristino.php dal branch modificaStampa, riusa $mysqli)
// F2.4 #91: unica implementazione vera in Salsiccia\Cassa\AdminView::repair()
// (src/Cassa/AdminView.php, logica via DbRepair::run() da F2.2, mai duplicata);
// qui resta solo lo shim con stessa firma. Delete legacy mai in F2 (§9).
function mostraRipristinaDb($mysqli)
{
    \Salsiccia\Cassa\AdminView::repair($mysqli);
}

// Modifica info festa: legge/scrive EVENT_NAME e DURATA_FESTA in storage/festa.json
// via festa_leggi()/festa_imposta() (F0.3, pattern T17); set.inc resta congelato
// read-only, mai rewrite. (port di 1.0/configurazione.php dal branch modificaStampa)
// F2.4 #91: unica implementazione vera in Salsiccia\Cassa\AdminView::info()
// (src/Cassa/AdminView.php, solo vista); qui resta solo lo shim con stessa
// firma. Delete legacy mai in F2 (§9).
function mostraModificaInfo()
{
    \Salsiccia\Cassa\AdminView::info();
}

// CAMBIA CARTA STAMPA (F3.4 #64, toggle continua <-> singoli): riusa F3.1
// (cartaStampaLeggiSetup) + F3.2 (PrintService::inviaSetupCarta) + F3.3
// (impostaCartaStampa). Setup/invio falliti => errore, modalita invariata.
// Pattern mostraRipristinaDb: isAdmin + POST + CSRF + confirm.
// F2.5 #92: unica implementazione vera in Salsiccia\Cassa\AdminView::carta()
// (src/Cassa/AdminView.php); qui resta solo lo shim con stessa firma.
// Delete legacy mai in F2 (§9).
function mostraPrintReset()
{
    \Salsiccia\Cassa\AdminView::carta();
}

// Selezione stampante (F4.5 #55, stato finale stagisti HEAD): valida contro
// gate printer_gate_allowed() (F4.1), salva in storage/stampante_selezione.json
// via printer_selection_file() (letta a boot in set.inc F4.4). Solo
// "DIRETTA"/"RETE", "ZPL"/"EPL", LOCK_EX. IP opzionale, solo RETE.
// F2.3 #90: unica implementazione vera in Salsiccia\Printer\PrinterConfig::salva()
// (src/Printer/PrinterConfig.php); qui resta solo lo shim con stessa firma
// e stessi esiti. Delete legacy mai in F2 (§9).
function impostaStampanteSelezionata($name, $conn, $lang, $ip = '')
{
    return \Salsiccia\Printer\PrinterConfig::salva($name, $conn, $lang, $ip);
}

// Raggiungibilita' veritiera per la pagina switch (F4.5 #55): DIRETTA da stato
// CUPS reale printer_lpstat_stato() + presenza USB lpinfo (F4.2) via
// printer_stato_locale_ok(); RETE ping-only via printer_is_reachable().
// Senza CUPS: rosso "non verificabile", mai fatal.
// F2.3 #90: unica implementazione vera in Salsiccia\Printer\PrinterConfig::
// raggiungibile(); qui resta solo lo shim con stessa firma e stessi esiti.
function stampante_switch_ok($nome, $conn, $ip = '')
{
    return \Salsiccia\Printer\PrinterConfig::raggiungibile($nome, $conn, $ip);
}

// Handler switch_printer (F2.3 #90, routes/cassa.php: handler => salvaStampante,
// view => switchPrinter, auth => admin; raggiunto via function_exists da
// CassaController::gestisci() e gestisciAzioni()): delega a PrinterConfig con
// gli stessi $_POST della vista (nome/conn/lingua/ip, regola RETE) e stesso
// blocco irraggiungibile; mai echo/redirect qui (gli esiti li mostra la vista).
function salvaStampante($mysqli = null, $id_cassa = null)
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['cambia_stampante']))
        return;
    if (!function_exists('csrf_ok') || !csrf_ok())
        return;
    if (!function_exists('isAdmin') || !isAdmin())
        return;
    $nome = (string)($_POST['nome'] ?? '');
    $conn = (string)($_POST['conn'] ?? '');
    $lingua = (string)($_POST['lingua'] ?? '');
    $ip = $conn === 'RETE' ? trim((string)($_POST['ip'] ?? '')) : '';
    $ipCheck = $conn === 'RETE' ? ($ip !== '' ? $ip : (defined('PRINTER_IP') ? PRINTER_IP : '')) : '';
    if (!\Salsiccia\Printer\PrinterConfig::raggiungibile($nome, $conn, $ipCheck))
        return;
    \Salsiccia\Printer\PrinterConfig::salva($nome, $conn, $lingua, $ip);
}

// CAMBIA STAMPANTE (F4.5 #55, stato finale stagisti HEAD 0632388): una card
// per stampante con pallino verde/rosso (stampante_switch_ok sopra), grid 2x2
// ($rpp=4), raggiungibili prime, senza riga STATO (272aee3). Solo la GX420t
// e' dual: scelta ZPL/EPL via pill, SELEZIONA disabilitato finche' non scegli;
// le altre mostrano badge fisso ZPL/EPL + SELEZIONA diretto. Selezione
// irraggiungibile bloccata (tranne RETE ping-only).
// Pattern mostraPrintReset: isAdmin + POST + CSRF + confirm + impostaStampanteSelezionata().
// CAMBIA STAMPANTE (F4.5 #55, stato finale stagisti HEAD 0632388): una card
// per stampante con pallino verde/rosso (stampante_switch_ok sopra), grid 2x2
// ($rpp=4), raggiungibili prime, senza riga STATO (272aee3). Solo la GX420t
// e' dual: scelta ZPL/EPL via pill, SELEZIONA disabilitato finche' non scegli;
// le altre mostrano badge fisso ZPL/EPL + SELEZIONA diretto. Selezione
// irraggiungibile bloccata (tranne RETE ping-only).
// Pattern mostraPrintReset: isAdmin + POST + CSRF + confirm + impostaStampanteSelezionata().
// F2.5 #92: unica implementazione vera in Salsiccia\Cassa\AdminView::switchPrinter()
// (src/Cassa/AdminView.php, scrittura via PrinterConfig F2.3); qui resta solo
// lo shim con stessa firma. Delete legacy mai in F2 (§9).
function mostraSwitchPrinter()
{
    \Salsiccia\Cassa\AdminView::switchPrinter();
}

// F2.1 #88: shim sottile verso StampaView/StampaController (unica implementazione vera in src/Cassa).
// Stesse firme, nessuna logica duplicata; delete legacy solo a migrazione completa (punto 9).
function mostraLayoutResto($totale, $pagato, $metodo, $base_url, $mostra_nuovo = true)
{
    \Salsiccia\Cassa\StampaView::resto((float)$totale, (float)$pagato, (string)$metodo, (string)$base_url, (bool)$mostra_nuovo);
}

// Stampa
function mostraSchermataStampa($mysqli)
{
    \Salsiccia\Cassa\StampaController::stampa($mysqli);
}
