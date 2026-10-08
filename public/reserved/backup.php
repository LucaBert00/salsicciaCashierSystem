<?php
declare(strict_types=1);
// reserved/backup.php — bottone dump fine-giornata intero DB (Decide #91, Task #96).
// Stesso auth + CSRF di visualizza.php (M1): login riservata, mutazioni solo
// via POST con token. Scrive fuori docroot, mai path web-diretti.
require_once __DIR__ . '/../../bootstrap.php';
\Salsiccia\Support\Session::start();
if (empty($_SESSION['reserved_auth']))
{
    header('Location: login.php?msg=2');
    exit;
}

require_once __DIR__ . '/../../src/Backup/BackupRun.php';

$messaggioStato = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['backup']))
{
    if (!csrf_ok())
    {
        $messaggioStato = 'TOKEN NON VALIDO';
    }
    else
    {
        $esito = \Salsiccia\Backup\BackupRun::run($mysqli, \Salsiccia\Backup\BackupRun::config());
        if (!empty($esito['ok']))
        {
            $messaggioStato = 'DUMP OK: ' . $esito['file']
                . ($esito['outside'] ? ' (fuori docroot: copiare su USB)' : ' (storage/ transitoria: copiare su USB e cancellare)')
                . ' in ' . $esito['dir'];
        }
        else
        {
            $messaggioStato = 'DUMP FALLITO: ' . $esito['msg'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BACKUP FINE-GIORNATA</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        main{align-items:center;text-align:center;width:100%;overflow-y:auto}
        main header{justify-content:center;margin:auto auto 12px}
        .admin-panel{align-items:center;flex-grow:0;max-width:560px;margin:0 auto auto;width:100%}
        .admin-panel form{display:flex;flex-direction:column;align-items:center;width:100%}
        .admin-btn-row{justify-content:center}
    </style>
</head>
<body>
    <main>
        <header><h1>BACKUP FINE-GIORNATA</h1></header>
        <section class="admin-panel">
            <?php if ($messaggioStato !== ''): ?>
                <h3 class="admin-group-title"><?php echo htmlspecialchars($messaggioStato); ?></h3>
            <?php endif; ?>
            <p class="admin-group-title">DUMP INTERO DB — RESPONSABILE A CASSA CHIUSA, POI COPIA SU USB</p>
            <form action="backup.php" method="post">
                <?php csrf_field(); ?>
                <div class="admin-btn-row">
                    <a href="visualizza.php" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="backup" class="opzione-btn">DUMP ORA</button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
