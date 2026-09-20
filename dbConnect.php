<?php
declare(strict_types=1);
require_once __DIR__ . '/env.inc';
// T10 fail-closed: nessun fallback credenziali. Recovery: copia .env.example in .env.
$dbName = trim((string)getenv('SALSICCIA_DB_NAME'));
$dbHost = trim((string)getenv('SALSICCIA_DB_HOST'));
$dbUser = trim((string)getenv('SALSICCIA_DB_USER'));
$dbPassword = (string)getenv('SALSICCIA_DB_PASS');
if ($dbHost === '' || $dbName === '' || $dbUser === '' || $dbPassword === '')
{
    http_response_code(500);
    die('Configurazione mancante: SALSICCIA_DB_HOST/NAME/USER/PASS non impostate. Copia .env.example in .env e compila i valori.');
}

$mysqli = mysqli_connect($dbHost, $dbUser, $dbPassword,$dbName)
or die(mysqli_connect_error());
?>

