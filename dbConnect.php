<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successore pianificato src/Support/Db.php (v. docs/ARCHITETTURA_REVISTA.md §6 e §9, fase 4); rimozione solo dopo la migrazione, rivalutare entro 2026-12-31.
// F5.5b #118: niente env.inc. Le credenziali sono lette dalle stesse chiavi di
// getenv di prima, con lo stesso loader .env: Env::carica() e' idempotente sulle
// env di sistema/Apache (che hanno precedenza), quindi i valori sono identici a
// quelli che leggeva il require di env.inc. require_once sull'autoload perche'
// questo file puo' essere richiesto da solo.
require_once __DIR__ . '/vendor/autoload.php';
\Salsiccia\Support\Env::carica();
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

