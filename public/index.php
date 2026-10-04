<?php declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';
// F3.1: front controller su require unico (docs/ARCHITETTURA_REVISTA.md §10.12a+§4).
// Bootstrap (autoload+ErrorHandler+set.inc+dbConnect+funzioni.inc+
// functionsFrontend.inc transitorio+CassaController+CassaView); qui solo
// dispatch + render, markup invariato.
// routes/cassa.php posseduta dal controller; tinta e match nella vista.
$action = isset($_GET['action']) ? $_GET['action'] : '';
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
