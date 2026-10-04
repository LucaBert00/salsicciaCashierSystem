<?php declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
// F3.2: front controller su require unico (docs/ARCHITETTURA_REVISTA.md §10.12+§4).
// Bootstrap (autoload+ErrorHandler+set.inc+dbConnect+funzioni.inc+
// functionsFrontend.inc isolabile+CassaController+CassaView); qui solo
// dispatch + render, markup invariato.
// routes/cassa.php posseduta dal controller; tinta e match nella vista.
// F3.2 #95: $cat/geometria per parametro esplicito (ex globali impliciti §3.2d
// da functionsFrontend.inc:58-67, ora solo flusso F1); $mysqli da bootstrap.
$action = isset($_GET['action']) ? $_GET['action'] : '';
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
if ($cat <= 0)
    $cat = defaultCat($mysqli);
$but_x_row = BOT_X_ROW;
$but_x_col = BOT_X_COL;
$view = \Salsiccia\Cassa\CassaController::gestisci($mysqli);
// Flag ok in $_GET che la vista non legge: c+ok senza login = errore kiosk, mai fatal.
if ($view === 'config' && $action === 'c' && isset($_GET['ok']) && !isAdmin()) {
    $view = 'config-errore';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salsiccia Cashier System</title>
    <link rel="icon" type="image/svg+xml" href="asset/favicon.svg">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php \Salsiccia\Cassa\CassaView::render($view, $mysqli, $cat, $but_x_row, $but_x_col); ?>
</body>
</html>
