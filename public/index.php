<?php declare(strict_types=1);
require_once __DIR__ . '/../src/Support/ErrorHandler.php';
\Salsiccia\Support\ErrorHandler::registra();
require_once __DIR__ . '/../functionsFrontend.inc'; ?>
<?php
$action = isset($_GET['action']) ? $_GET['action'] : '';
$isAdmin = isAdmin();
// T23: validita via tabella (routes/cassa.php); non listata = schermata errore kiosk, mai fatal.
$tabellaCassa = (array)require __DIR__ . '/../routes/cassa.php';
$azioneNonValida = is_string($action) && $action !== '' && !array_key_exists($action, $tabellaCassa);
// Vecchi bookmark ?action=c&ok&code= rifiutati: code in URL non abilita piu nulla (issue).
$showModifica = $action == 'm';
$showOpzioni = $action == 'o';
$showConfig = $action == 'c';
$showAdmin = $action == 'c' && isset($_GET['ok']) && $isAdmin;
$showStampa = $action == 's';
$showStandby = $action == 'standby';
$showRepair = $action == 'repair' && $isAdmin;
$showContatori = $action == 'contatori' && $isAdmin;
$showPrintReset = $action == 'print_reset' && $isAdmin;
$showSwitchPrinter = $action == 'switch_printer' && $isAdmin;
// Sfondo tinta categoria solo sulla schermata principale cassa, mai su admin/stampa/modifica/standby.
$isCassaMain = !$azioneNonValida && !$showAdmin && !$showStampa && !$showModifica && !$showOpzioni && !$showStandby && !$showRepair && !$showContatori && !$showPrintReset && !$showSwitchPrinter && !($showConfig && isset($_GET['ok']) && !$showAdmin);
$hexCassa = '#e7e9eb';
if ($isCassaMain)
{
    $cat = (int)$cat;
    // T19: tinta via CatalogRepo (stessa riga categoria del ramo vuoto).
    $rcCassa = (new \Salsiccia\Catalog\CatalogRepo($mysqli))->categoria($cat);
    if ($rcCassa && isset($rcCassa['colore'])) $hexCassa = coloreCategoriaHex($rcCassa['colore']);
}
// Schermate sensibili senza flag admin -> login, mai cassa muta ne accesso anonimo.
if (!$isAdmin && in_array($action, array('repair', 'print_reset', 'contatori', 'switch_printer'), true))
{
    header("Location: index.php?action=c");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAKE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main<?php if (!empty($isCassaMain)) echo ' class="cassa-c" style="--hex-cat:' . $hexCassa . ';"'; ?>>
        <?php if ($showAdmin): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>PANNELLO ADMIN</h1></header>
            <?php mostraPannelloAdmin(); ?>
        <?php elseif ($showStampa): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <?php mostraSchermataStampa($mysqli); ?>
        <?php elseif ($showModifica): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>MODIFICA ORDINE</h1></header>
            <?php mostraModificaOrdine($mysqli); ?>
            <div class="footer"><?php mostraFooterModifica($cat); ?></div>
        <?php elseif ($showOpzioni): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>TIPOLOGIA ORDINE</h1></header>
            <?php mostraOpzioni(); ?>
            <div class="footer"><?php mostraFooterModifica($cat); ?></div>
        <?php elseif ($showStandby): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>ORDINI IN STANDBY</h1></header>
            <?php mostraOrdiniStandby($mysqli); ?>
        <?php elseif ($showRepair): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>RIPRISTINA DB</h1></header>
            <?php mostraRipristinaDb($mysqli); ?>
        <?php elseif ($showContatori): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>CONTATORI</h1></header>
            <?php mostraContatori($mysqli); ?>
        <?php elseif ($showPrintReset): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>CAMBIA CARTA STAMPA</h1></header>
            <?php mostraPrintReset(); ?>
        <?php elseif ($showSwitchPrinter): ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1>CAMBIA STAMPANTE</h1></header>
            <?php mostraSwitchPrinter(); ?>
        <?php elseif ($showConfig && isset($_GET['ok']) && !$showAdmin): ?>
            <section style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex-grow:1;">
                <p style="font-size:24px;font-weight:800;color:#e54b3c;margin-bottom:30px;">ERRORE CODICE DI AUTORIZZAZIONE ERRATO</p>
            </section>
        <?php elseif ($azioneNonValida): ?>
            <?php \Salsiccia\Support\ErrorHandler::mostraErrore('AZIONE NON VALIDA'); ?>
        <?php else: ?>
            <nav><?php mostraNavCategorie($mysqli, $cat); ?><?php mostraIndicatoreTipo($mysqli); ?></nav>
            <header><h1><?php mostraTitolo($mysqli, $cat); ?></h1></header>
            <?php mostraBarraBarcode($mysqli, $cat); ?>
            <section id="main"><?php mostraTabellaProdotti($mysqli, $cat, $but_x_row, $but_x_col); ?></section>
            <div class="footer"><?php mostraFooterBottoni($cat); ?></div>
        <?php endif; ?>
    </main>
    <aside>
        <header>
            <h2>RIEPILOGO ORDINE</h2>
            <div class="order-summary-box"><?php mostraBoxRiepilogo($mysqli); ?></div>
            <div class="aside-actions"><?php mostraAzioniLaterali(); ?></div>
        </header>
        <?php if ($showConfig && !$showAdmin): ?>
            <section style="flex-grow:1;"><?php mostraPannelloConfig(); ?></section>
        <?php else: ?>
            <section>
                <h3>RIEPILOGO PRODOTTI</h3>
                <ul class="cart-list"><?php mostraListaProdotti($mysqli); ?></ul>
            </section>
        <?php endif; ?>
        <?php mostraAzioniExtra(); ?>
    </aside>
</body>
</html>
