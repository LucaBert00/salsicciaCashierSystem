<?php
require_once __DIR__ . '/env.inc';
// Issue [C3]: segreti da env con fallback fiera (niente nuove dipendenze).
// Produzione: SALSICCIA_DB_* via env Apache/sistema; fiera XAMPP: fallback sotto.
$dbName = getenv('SALSICCIA_DB_NAME');
if (!$dbName)
{
    $dbName = "salsiccia";
}

$dbHost = getenv('SALSICCIA_DB_HOST');
if (!$dbHost)
{
    $dbHost = "localhost";
}

$dbUser = getenv('SALSICCIA_DB_USER');
if (!$dbUser)
{
    $dbUser = "salsiccia";
}

$dbPassword = getenv('SALSICCIA_DB_PASS');
if (!$dbPassword)
{
    $dbPassword = "Salsiccia@123";
}

$mysqli = mysqli_connect($dbHost, $dbUser, $dbPassword,$dbName)
or die(mysqli_connect_error());
?>

