<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successore pianificato src/Backup/BackupRun.php (§7); rimozione solo dopo la migrazione, rivalutare entro 2026-12-31.
if (!function_exists('cassa_log'))
    require_once __DIR__ . '/env.inc';

// Backup fine-giornata: dump intero DB fuori docroot (mappa #87, Decide #91, Task #96).
// Manuale, dal responsabile a cassa chiusa: niente cron, niente dump a ogni scontrino.
// Strumento: mysqldump se disponibile, fallback dump via PHP se assente.
// Credenziali solo da env SALSICCIA_DB_* (mai in chiaro qui); il dump non e'
// mai raggiungibile via HTTP (storage/dumps fuori docroot, coperto da
// .htaccess dedicato + deny .sql in root).
// Env opzionali: SALSICCIA_DUMP_DIR (path assoluto fuori docroot),
// SALSICCIA_MYSQLDUMP (path binario mysqldump).

// Config da env. Stringhe vuote = mysqldump salta, il fallback PHP usa la
// connessione $mysqli gia' aperta (nessun segreto duplicato da dbConnect.php).
function backup_config(): array
{
    return array(
        'dir' => trim((string)(getenv('SALSICCIA_DUMP_DIR') ?: '')),
        'mysqldump' => trim((string)(getenv('SALSICCIA_MYSQLDUMP') ?: '')),
        'db_host' => (string)(getenv('SALSICCIA_DB_HOST') ?: ''),
        'db_name' => (string)(getenv('SALSICCIA_DB_NAME') ?: ''),
        'db_user' => (string)(getenv('SALSICCIA_DB_USER') ?: ''),
        'db_pass' => (string)(getenv('SALSICCIA_DB_PASS') ?: ''),
    );
}

// Default fuori docroot: storage/dumps alla radice (fuori docroot da T16,
// DocumentRoot -> public/), mai dentro il docroot servito da Apache.
function backup_default_dir(): string
{
    return __DIR__ . '/storage/dumps';
}

// Cartella di destinazione: fuori docroot se scrivibile (storage/dumps;
// SALSICCIA_DUMP_DIR assoluto per override fiera), altrimenti storage/dumps
// transitoria con .htaccess deny. Ritorna path assoluto + flag outside.
function backup_resolve_dir(array $cfg): array
{
    $cand = ($cfg['dir'] !== '') ? $cfg['dir'] : backup_default_dir();
    if (!preg_match('#^([A-Za-z]:[\\\\/]|/)#', $cand))
    {
        $cand = backup_default_dir();
    }
    if (!is_dir($cand))
    {
        mkdir($cand, 0770, true);
    }
    if (is_dir($cand) && is_writable($cand))
    {
        return array('path' => $cand, 'outside' => true);
    }
    $fb = __DIR__ . '/storage/dumps';
    if (!is_dir($fb))
    {
        mkdir($fb, 0770, true);
    }
    if (file_put_contents($fb . '/.htaccess', "<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n") === false)
        cassa_log('warning', 'backup htaccess scrittura fallita');
    return array('path' => $fb, 'outside' => true);
}

// Nome data-evento non indovinabile (#91): data + 8 hex random, solo basename.
function backup_filename(): string
{
    return 'salsiccia-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.sql';
}

// Binario mysqldump: env, poi XAMPP fiera, poi PATH.
function backup_mysqldump_bin(array $cfg): string
{
    if ($cfg['mysqldump'] !== '' && is_file($cfg['mysqldump']))
    {
        return $cfg['mysqldump'];
    }
    if (is_file('C:\\xampp\\mysql\\bin\\mysqldump.exe'))
    {
        return 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    }
    return 'mysqldump';
}

// Dump via mysqldump. Password solo in env di processo (MYSQL_PWD), mai in
// chiaro sulla riga di comando (visibile in lista processi). Ritorna true se
// il file esiste e non e' vuoto.
function backup_via_mysqldump(array $cfg, string $file): bool
{
    if ($cfg['db_name'] === '' || $cfg['db_user'] === '')
    {
        return false;
    }
    $cmd = escapeshellarg(backup_mysqldump_bin($cfg))
        . ' --host=' . escapeshellarg($cfg['db_host'] !== '' ? $cfg['db_host'] : 'localhost')
        . ' --user=' . escapeshellarg($cfg['db_user'])
        . ' --single-transaction --skip-lock-tables --routines'
        . ' --default-character-set=utf8mb4'
        . ' ' . escapeshellarg($cfg['db_name'])
        . ' --result-file=' . escapeshellarg($file);
    putenv('MYSQL_PWD=' . $cfg['db_pass']);
    $proc = proc_open($cmd, array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w')), $pipes);
    if (!is_resource($proc))
    {
        putenv('MYSQL_PWD');
        return false;
    }
    fclose($pipes[0]);
    stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $rc = proc_close($proc);
    putenv('MYSQL_PWD');
    return $rc === 0 && is_file($file) && filesize($file) > 0;
}

// Fallback dump via PHP (mysqldump assente): schema + INSERT per ogni tabella.
// Nomi tabelle da SHOW TABLES, riusati solo se [A-Za-z0-9_], mai interpolati grezzi.
function backup_via_php(mysqli $db, string $db_name, string $file): bool
{
    $res = $db->query('SHOW TABLES');
    if ($res === false)
    {
        return false;
    }
    $tabelle = array();
    while ($r = $res->fetch_row())
    {
        $tabelle[] = $r[0];
    }
    $res->free();
    if ($tabelle === array())
    {
        return false;
    }
    $fh = fopen($file, 'wb');
    if ($fh === false)
    {
        cassa_log('error', 'backup apertura dump fallita');
        return false;
    }
    fwrite($fh, '-- SalsicciaStagisti dump intero DB `' . $db_name . '` del ' . date('c') . " (fallback PHP)\n\n");
    foreach ($tabelle as $tab)
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $tab))
        {
            continue;
        }
        $ddl = $db->query('SHOW CREATE TABLE `' . $tab . '`');
        if ($ddl === false)
        {
            continue;
        }
        $row = $ddl->fetch_row();
        $ddl->free();
        fwrite($fh, 'DROP TABLE IF EXISTS `' . $tab . "`;\n" . $row[1] . ";\n");
        $righe = $db->query('SELECT * FROM `' . $tab . '`');
        if ($righe === false)
        {
            continue;
        }
        while ($riga = $righe->fetch_row())
        {
            $vals = array();
            foreach ($riga as $v)
            {
                $vals[] = ($v === null) ? 'NULL' : ("'" . $db->real_escape_string((string)$v) . "'");
            }
            fwrite($fh, 'INSERT INTO `' . $tab . '` VALUES (' . implode(',', $vals) . ");\n");
        }
        $righe->free();
        fwrite($fh, "\n");
    }
    fclose($fh);
    return is_file($file) && filesize($file) > 0;
}

// Un dump intero: mysqldump, poi fallback PHP. Mai eccezioni: esito array.
// ok=true: ['file' => basename, 'dir' => path assoluto (testo, mai link web), 'outside' => bool].
function backup_run(mysqli $db, array $cfg): array
{
    $dest = backup_resolve_dir($cfg);
    if (!is_dir($dest['path']) || !is_writable($dest['path']))
    {
        return array('ok' => false, 'msg' => 'cartella dump non scrivibile');
    }
    $file = $dest['path'] . '/' . backup_filename();
    $db_name = ($cfg['db_name'] !== '') ? $cfg['db_name'] : 'salsiccia';
    if (!backup_via_mysqldump($cfg, $file) && !backup_via_php($db, $db_name, $file))
    {
        return array('ok' => false, 'msg' => 'dump fallito (mysqldump + PHP)');
    }
    return array('ok' => true, 'file' => basename($file), 'dir' => $dest['path'], 'outside' => $dest['outside']);
}
