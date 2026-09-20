<?php require_once 'functionsFrontend.inc'; ?>
<?php
$action = isset($_GET['action']) ? $_GET['action'] : '';
$isAdmin = isAdmin();
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
// Sfondo tinta categoria solo sulla schermata principale cassa, mai su admin/stampa/modifica/standby.
$isCassaMain = !$showAdmin && !$showStampa && !$showModifica && !$showOpzioni && !$showStandby && !$showRepair && !$showContatori && !$showPrintReset && !($showConfig && isset($_GET['ok']) && !$showAdmin);
$hexCassa = '#e7e9eb';
if ($isCassaMain)
{
    $cat = (int)$cat;
    $qc = db_select($mysqli, "SELECT colore FROM categorie WHERE id_categoria = ? LIMIT 1", 'i', array($cat));
    if ($qc && ($rc = mysqli_fetch_array($qc))) $hexCassa = coloreCategoriaHex($rc['colore']);
}
// Schermate sensibili senza flag admin -> login, mai cassa muta ne accesso anonimo.
if (!$isAdmin && in_array($action, array('repair', 'print_reset', 'contatori'), true))
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
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>PANNELLO ADMIN</h1></header>
            <?php mostraPannelloAdmin(); ?>
        <?php elseif ($showStampa): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <?php mostraSchermataStampa(); ?>
        <?php elseif ($showModifica): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>MODIFICA ORDINE</h1></header>
            <?php mostraModificaOrdine(); ?>
            <div class="footer"><?php mostraFooterModifica(); ?></div>
        <?php elseif ($showOpzioni): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>TIPOLOGIA ORDINE</h1></header>
            <?php mostraOpzioni(); ?>
            <div class="footer"><?php mostraFooterModifica(); ?></div>
        <?php elseif ($showStandby): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>ORDINI IN STANDBY</h1></header>
            <?php mostraOrdiniStandby(); ?>
        <?php elseif ($showRepair): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>RIPRISTINA DB</h1></header>
            <?php mostraRipristinaDb(); ?>
        <?php elseif ($showContatori): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>CONTATORI</h1></header>
            <?php mostraContatori(); ?>
        <?php elseif ($showPrintReset): ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1>PRINT RESET</h1></header>
            <?php mostraPrintReset(); ?>
        <?php elseif ($showConfig && isset($_GET['ok']) && !$showAdmin): ?>
            <section style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex-grow:1;">
                <p style="font-size:24px;font-weight:800;color:#e54b3c;margin-bottom:30px;">ERRORE CODICE DI AUTORIZZAZIONE ERRATO</p>
            </section>
        <?php else: ?>
            <nav><?php mostraNavCategorie(); ?><?php mostraIndicatoreTipo(); ?></nav>
            <header><h1><?php mostraTitolo(); ?></h1></header>
            <section id="main"><?php mostraTabellaProdotti(); ?></section>
            <div class="footer"><?php mostraFooterBottoni(); ?></div>
        <?php endif; ?>
    </main>
    <aside>
        <header>
            <h2>RIEPILOGO ORDINE</h2>
            <div class="order-summary-box"><?php mostraBoxRiepilogo(); ?></div>
            <div class="aside-actions"><?php mostraAzioniLaterali(); ?></div>
        </header>
        <?php if ($showConfig && !$showAdmin): ?>
            <section style="flex-grow:1;"><?php mostraPannelloConfig(); ?></section>
        <?php else: ?>
            <section>
                <h3>RIEPILOGO PRODOTTI</h3>
                <ul class="cart-list"><?php mostraListaProdotti(); ?></ul>
            </section>
        <?php endif; ?>
        <?php mostraAzioniExtra(); ?>
    </aside>
</body>
</html>
