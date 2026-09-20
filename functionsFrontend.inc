<?php
declare(strict_types=1);

require_once __DIR__ . '/set.inc';
require_once __DIR__ . '/dbConnect.php';
require_once __DIR__ . '/funzioni.inc';
require_once __DIR__ . '/fiscale.inc';
require_once __DIR__ . '/src/Cassa/OrderService.php';
require_once __DIR__ . '/src/Catalog/CatalogRepo.php';
require_once __DIR__ . '/src/Catalog/CatalogView.php';

// Auth admin cassa (issue): sessione come reserved/login.php, mai code in URL.
if (session_status() === PHP_SESSION_NONE)
    session_start();

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

// Azioni del riepilogo
gestisciAzioni();

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

function gestisciAzioni()
{
    global $mysqli, $id_cassa, $HTTP_GET_VARS;

    // Login admin via POST (issue): code mai in URL/log; OK -> flag sessione + redirect pulito.
    if (($_SERVER['REQUEST_METHOD'] ?? '') == 'POST' && ($HTTP_GET_VARS['action'] ?? '') == 'c' && isset($_POST['code']))
    {
        if (!empty($_SESSION['admin_block_until']) && time() < $_SESSION['admin_block_until'])
        {
            header("Location: index.php?action=c&ok=1&err=1");
            exit;
        }
        if (password_verify((string)$_POST['code'], (string)PWD_HASH))
        {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            unset($_SESSION['admin_fail'], $_SESSION['admin_block_until']);
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
            header("Location: index.php?action=c&ok=1&err=1");
        }
        exit;
    }

    if (isset($HTTP_GET_VARS['action']) && $HTTP_GET_VARS['action'] == 'logout')
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
        header("Location: index.php");
        exit;
    }

    // Scelta esplicita cassa da pannello admin: salva in sessione, mai da IP.
    if (($_SERVER['REQUEST_METHOD'] ?? '') == 'POST' && ($HTTP_GET_VARS['action'] ?? '') == 'cassa' && isset($_POST['id_cassa']))
    {
        if (!isAdmin() || !csrf_ok())
        {
            header("Location: index.php?action=c");
            exit;
        }
        $c = (int)$_POST['id_cassa'];
        if ($c >= 1 && $c <= 999)
            $_SESSION['id_cassa'] = $c;
        $id_cassa = cassaCorrente();
        header("Location: index.php?action=c&ok=1");
        exit;
    }

    // Toggle fiera da pannello admin: solo POST con token, GET resta lettura.
    if (($HTTP_GET_VARS['action'] ?? '') == 'fiera')
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

    if (isset($HTTP_GET_VARS['action']))
    {
        $action = $HTTP_GET_VARS['action'];
    }
    else
    {
        $action = '';
    }

    // T14: mutazioni cassa solo via POST con token (come fiera/cassa sopra);
    // GET resta lettura: prefetch/link non mutano mai l'ordine live.
    $post_ok = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') && csrf_ok();

    // Flussi ordine (T18): logica in Salsiccia\Cassa\OrderService con DB iniettato;
    // qui resta solo HTTP/CSRF/redirect (thin caller).
    if ($action == 'r' && $post_ok) // annulla ordine
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->annullaOrdine();
    }

    if ($action == 'a' && $post_ok && isset($HTTP_GET_VARS['id'])) // aggiungi prodotto
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->aggiungiProdotto((int)$HTTP_GET_VARS['id']);
    }

    if ($action == 'mq' && $post_ok && isset($HTTP_GET_VARS['id']) && isset($HTTP_GET_VARS['qta']))
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->impostaQuantita((int)$HTTP_GET_VARS['id'], (int)$HTTP_GET_VARS['qta']);

        header("Location: ?action=m");
        exit;
    }

    if ($action == 'mr' && $post_ok && isset($HTTP_GET_VARS['id']))
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->rimuoviRiga((int)$HTTP_GET_VARS['id']);

        header("Location: ?action=m");
        exit;
    }

    if ($action == 'st' && $post_ok && isset($HTTP_GET_VARS['t']))
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->impostaTipo((string)$HTTP_GET_VARS['t']);

        header("Location: ?action=o");
        exit;
    }

    if ($action == 'sb' && $post_ok) // set ordine in standby
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->mettiStandby();
        header("Location: index.php");
        exit;
    }

    if ($action == 'ra' && $post_ok && isset($HTTP_GET_VARS['id'])) // reactivate da standby
    {
        (new \Salsiccia\Cassa\OrderService($mysqli, (int)$id_cassa))->riattiva((int)$HTTP_GET_VARS['id']);
        header("Location: index.php");
        exit;
    }
}

// pulsanti categorie (T19: thin caller, query in CatalogRepo, HTML in CatalogView).
function mostraNavCategorie()
{
    global $mysqli, $cat;
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    \Salsiccia\Catalog\CatalogView::navCategorie($repo->allCategorie(), (int)$cat);
}

//Categoria corrente (T19: thin caller).
function mostraTitolo()
{
    global $mysqli, $cat;
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    \Salsiccia\Catalog\CatalogView::titolo($repo->descrizioneCategoria((int)$cat));
}

//indicatore tipo ordine nav
function mostraIndicatoreTipo()
{
    global $mysqli;
    $id_cassa = cassaCorrente();

    $ris = db_select($mysqli, "SELECT tipo FROM ordini WHERE id_cassa = ? AND chiuso = '0' LIMIT 1", 'i', array($id_cassa));

    if ($ris && mysqli_num_rows($ris) > 0)
    {
        $riga = mysqli_fetch_array($ris);
        $tipo = $riga['tipo'];

        $lettere = array('pre' => 'P', 'mus' => 'M', 'stf' => 'S', 'asp' => 'A');

        if (isset($lettere[$tipo]))
            echo '<span style="color:#e54b3c;font-weight:800;font-size:18px;margin-left:auto;">' . $lettere[$tipo] . '</span>';
    }
}

// tabella prodotti (T19: thin caller, query in CatalogRepo, HTML in CatalogView).
function mostraTabellaProdotti()
{
    global $mysqli, $cat, $but_x_row, $but_x_col;

    $cat = (int)$cat;
    $repo = new \Salsiccia\Catalog\CatalogRepo($mysqli);
    $prodotti = $repo->prodottiByCategoria($cat);
    $vuota = count($prodotti) === 0 ? $repo->categoria($cat) : null;
    \Salsiccia\Catalog\CatalogView::tabellaProdotti($prodotti, $vuota, $cat, (int)$but_x_row, (int)$but_x_col);
}

//Modifica ordine
function mostraModificaOrdine()
{
    global $mysqli;
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

// Footer modifica
function mostraFooterModifica()
{
    global $cat;
    echo '<form method="post" action="?cat=' . (int)$cat . '&action=r" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" id="annulla-btn"><span id="annullaOrdine-sign">🗑️️ ANNULLA ORDINE</span></button></form>
          <a href="index.php" id="continua-btn" style="text-decoration:none"><span id="continua-sign">🛒 CONTINUA ORDINE</span></a>
          <a href="?action=s" id="stampa-btn" style="text-decoration:none"><span id="stampa-sign">🖨️ STAMPA</span></a>';
}

// schermata opzioni
function mostraOpzioni()
{
    echo '<section class="opzioni-section">';
    echo '<div class="opzioni-row-1">';
    foreach (array('pre' => 'PREVENDITA', 'mus' => 'MUSICISTI', 'stf' => 'STAFF', 'asp' => 'ASPORTO') as $t => $label)
    {
        echo '<form method="post" action="?action=st&t=' . $t . '" style="display:contents;">';
        csrf_field();
        echo '<button type="submit" class="opzione-btn">' . $label . '</button></form>';
    }
    echo '</div>';
    echo '<div class="opzioni-row-2">';
    echo '<form method="post" action="?action=st&t=nor" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" class="opzione-btn opzione-btn-large">NORMALE</button></form>';
    echo '<form method="post" action="?action=sb" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" class="opzione-btn opzione-btn-large">STAND BY</button></form>';
    echo '</div>';
    echo '</section>';
}

function mostraOrdiniStandby()
{
    global $mysqli;

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
function mostraFooterBottoni()
{
    global $cat;
    echo '<form method="post" action="?cat=' . (int)$cat . '&action=r" style="display:contents;">';
    csrf_field();
    echo '<button type="submit" id="annulla-btn"><span id="annullaOrdine-sign">🗑️️ ANNULLA ORDINE</span></button></form>
           <button id="stampa-btn" onclick="location.href=\'?action=s\'"><span id="stampa-sign">🖨️ STAMPA</span></button>';
}

//R iepilogo ordine
function mostraBoxRiepilogo()
{
    global $mysqli;
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
function mostraListaProdotti()
{
    global $mysqli;
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
    echo '<a href="?action=print_reset" class="opzione-btn">PRINT RESET</a>';
    echo '<a href="?action=restart" class="opzione-btn danger">RIAVVIA</a>';
    echo '<a href="?action=shutdown" class="opzione-btn danger">SPEGNI</a>';
    echo '</div>';
    echo '</div>';

    echo '<div>';
    echo '<h3 class="admin-group-title">STAMPANTE SELEZIONATA: ' . PRINTER_NAME . ' - ' . PRINTER_CONNECTION . ' - ' . PRINTER_LANGUAGE . '</h3>';
    echo '<div class="admin-btn-row">';
    echo '<a href="stampante_continua.php" class="opzione-btn">STAMPANTE CONTINUA</a>';
    echo '<a href="index.php" class="opzione-btn">CONTINUA ORDINE</a>';
    echo '<a href="?action=logout" class="opzione-btn">ESCI</a>';
    echo '</div>';
    echo '</div>';

    echo '</section>';
}

// Schermata unica CONTATORI: tutti i venduti monitorati con nome + quantita (riusa conta_prodotto).
function mostraContatori()
{
    global $mysqli;
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
    $totRis = mysql_query_safe($mysqli, "SELECT COUNT(*) AS c FROM `contatori` WHERE attivo = 'T'");
    $totRiga = mysqli_fetch_array($totRis);
    $tot = $totRiga ? (int)$totRiga['c'] : 0;
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
        $ris = db_select($mysqli, "SELECT id_contatore FROM `contatori` WHERE attivo = 'T' ORDER BY `nome` ASC LIMIT ?, ?", 'ii', array((int)$offset, (int)$rpp));
        echo '<table class="modifica-table">';
        echo '<thead><tr><th class="intestazione-tabella-descrizione">NOME PRODOTTO</th><th class="intestazione-tabella-quantita">QUANTITA VENDUTI</th></tr></thead>';
        echo '<tbody>';
        while ($ris && ($riga = mysqli_fetch_array($ris)))
        {
            $c = conta_prodotto((int)$riga['id_contatore'], $mysqli);
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
function mostraRipristinaDb()
{
    global $mysqli;
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
                error_log("REPAIR bloccato: tabella non whitelistata");
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

// Print reset: sblocca le Zebra quando si impiantano (cancella buffer + reset),
// riusa invia_file_stampa() cosi DIRETTA/RETE restano quelle di set.inc
function mostraPrintReset()
{
    // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
    if (!isAdmin())
    {
        header("Location: index.php?action=c");
        exit;
    }
    echo '<section class="admin-panel">';
    echo '<h3 class="admin-group-title">PRINT RESET</h3>';
    if (isset($_POST['print_reset']))
    {
        if (!csrf_ok())
        {
            echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
        else
        {
        // ZPL ~JA cancella tutto + ~JR reset; EPL N pulisce il buffer (zebra.docs)
        if (PRINTER_LANGUAGE == 'ZPL')
        {
            $cmd = "~JA\n~JR\n";
        } else
        {
            $cmd = "N\n";
        }

        //salva il comando di reset nel file di stampa.
        if (file_put_contents(LABELS_FILE, $cmd) === false)
            error_log("print_reset: scrittura " . LABELS_FILE . " fallita");

        if (invia_file_stampa())
        {
            echo '<p style="background:#5cb85c;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">Reset inviato a ' . PRINTER_NAME . '.</p>';
        }
        else
        {
            echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">STAMPANTE NON RAGGIUNGIBILE, reset non inviato.</p>';
        }
        echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
    }
    else
    {
        echo '<p style="color:#e54b3c;font-weight:800;">ATTENZIONE: verr&agrave; inviato il reset alla stampante ' . PRINTER_NAME . '.</p>';
        echo '<form method="post" action="?action=print_reset" onsubmit="return confirm(\'Confermi il reset della stampante?\');">';
        csrf_field();
        echo '<div class="admin-btn-row">';
        echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
        echo '<button type="submit" name="print_reset" value="1" class="opzione-btn">RESET STAMPANTE</button>';
        echo '</div>';
        echo '</form>';
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
    $metodo = ($metodo === 'contanti' || $metodo === 'carta') ? $metodo : '';
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

    //metodo pagamento
    echo '<p class="metodo-titolo">STAMPA SCONTRINO FISCALE, METODO DI PAGAMENTO:</p>';
    echo '<div class="metodo-grid">';
    $attivo_contanti = ($metodo == 'contanti') ? ' attivo' : '';
    $attivo_carta = ($metodo == 'carta') ? ' attivo' : '';
    echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $pagato . '&metodo=contanti\'" class="metodo-btn' . $attivo_contanti . '">';
    echo '<span class="metodo-btn-icona">💵</span>';
    echo '<span class="metodo-btn-label">CONTANTI</span></button>';
    echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $pagato . '&metodo=carta\'" class="metodo-btn' . $attivo_carta . '">';
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
function mostraSchermataStampa()
{
    global $mysqli;
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
        // Whitelist come sotto: $metodo finisce in onclick, mai grezzo.
        $metodo_err = (isset($_GET['metodo']) && in_array($_GET['metodo'], array('contanti', 'carta'), true)) ? $_GET['metodo'] : '';
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

        $ret_val = genera_file_stampa($ris, $numero_righe);
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
    // Whitelist come $validi/$tipo in gestisciAzioni: $metodo finisce in onclick, mai grezzo.
    $metodo = (isset($_GET['metodo']) && in_array($_GET['metodo'], array('contanti', 'carta'), true)) ? $_GET['metodo'] : '';
    $base_url = '?action=s&stampato=' . $id_ordine . '&tot=' . $totale;

    // Scontrino fiscale non bloccante (#92): al primo metodo scelto emette verso il
    // registratore o accoda; la schermata resto si mostra sempre e comunque.
    if ($metodo !== '')
    {
        try
        {
            $fisc = fiscale_emetti_scontrino($mysqli, $id_ordine, $metodo);
            if (empty($fisc['ok']) && !empty($fisc['fallback']))
                echo '<p style="background:#f0ad4e;color:#fff;font-weight:800;text-align:center;padding:10px;border-radius:6px;">REGISTRATORE NON RISPONDE: scontrino in coda, la vendita prosegue.</p>';
        }
        catch (Throwable $e)
        {
            error_log('fiscale non bloccante: ' . $e->getMessage());
        }
    }

    mostraLayoutResto($totale, $pagato, $metodo, $base_url);
}
