<?php
// reserved/login.php — ingresso area riservata
// Stesso login di login.php ma funzionante: POST su se stesso, prepared statements, flag $_SESSION['reserved_auth'] per reserved/visualizza.php.
session_start();

require_once __DIR__ . '/../dbConnect.php';
require_once __DIR__ . '/auth_password.inc';

if (isset($_POST['bot']))
{
    // rate-limit in sessione (5 fail -> 60s), upgrade a IP/file se serve anti-distribuito.
    if (!empty($_SESSION['reserved_block_until']) && time() < $_SESSION['reserved_block_until'])
    {
        header('Location: login.php?msg=er');
        exit;
    }
    // T12: fetch-then-verify in PHP. Nuovi hash Argon2id via password_verify;
    // righe legacy MySQL PASSWORD() accettate una volta e ri-hashate ad Argon2id
    // (migrazione senza lockout). Nessun PASSWORD()/SHA1 in SQL.
    $loginId = isset($_POST['id']) ? (string)$_POST['id'] : '';
    $loginPwd = isset($_POST['pwd']) ? (string)$_POST['pwd'] : '';
    $stored = null;
    $stmt = $mysqli->prepare("SELECT `PASSWORD` FROM `login` WHERE user_id = ?");
    $stmt->bind_param('s', $loginId);
    $stmt->execute();
    $stmt->bind_result($stored);
    $found = $stmt->fetch() && $stored !== null && $stored !== '';
    $stmt->close();

    $ok = $found && salsiccia_password_verify($loginPwd, (string)$stored);
    if ($ok && salsiccia_password_needs_rehash((string)$stored))
    {
        $newHash = salsiccia_password_hash($loginPwd);
        $up = $mysqli->prepare("UPDATE `login` SET `PASSWORD` = ? WHERE user_id = ?");
        $up->bind_param('ss', $newHash, $loginId);
        $up->execute();
        $up->close();
    }

    if ($ok)
    {
        session_regenerate_id(true);
        $_SESSION['reserved_auth'] = true;
        unset($_SESSION['reserved_fail'], $_SESSION['reserved_block_until']);
        header('Location: visualizza.php');
    }
    else
    {
        $_SESSION['reserved_fail'] = isset($_SESSION['reserved_fail']) ? (int)$_SESSION['reserved_fail'] + 1 : 1;
        if ($_SESSION['reserved_fail'] >= 5)
        {
            $_SESSION['reserved_block_until'] = time() + 60;
            unset($_SESSION['reserved_fail']);
        }
        header('Location: login.php?msg=er');
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Riservata</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        main{align-items:center;text-align:center;width:100%;overflow-y:auto}
        main header{justify-content:center;margin:auto auto 12px}
        .admin-panel{align-items:center;flex-grow:0;max-width:420px;margin:0 auto auto;width:100%}
        .admin-panel form{display:flex;flex-direction:column;align-items:center;width:100%}
        .admin-panel .codice-text-field{max-width:320px}
        .admin-btn-row{justify-content:center}
    </style>
</head>
<body>
    <main>
        <header><h1>LOGIN RISERVATA</h1></header>
        <section class="admin-panel">
            <?php if (isset($_GET['msg']) && ($_GET['msg'] === '2' || $_GET['msg'] === 'er')): ?>
                <h3 class="admin-group-title"><?php echo $_GET['msg'] === '2' ? 'ACCESSO NON AUTORIZZATO: EFFETTUARE IL LOGIN' : 'ERRORE: USERID E PASSWORD NON CORRETTI'; ?></h3>
            <?php endif; ?>
            <form action="login.php" method="post" id="form-login">
                <h3 class="admin-group-title">USER ID</h3>
                <input type="text" name="id" class="codice-text-field" maxlength="50" required>
                <h3 class="admin-group-title">PASSWORD</h3>
                <input type="password" name="pwd" class="codice-text-field" required>
                <!-- Tastiera touch (issue, variante A da issue come categorie): details espandibile, tasti 48px, focus-target USER ID/PASSWORD -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kb-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kb-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-action-btn" data-kb-shift style="flex:1;height:48px;font-size:16px;">SHIFT</button>
                            <button type="button" class="tastierino-btn" data-kb-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row" style="margin-top:20px;">
                    <button type="submit" name="bot" class="opzione-btn">OK</button>
                    <a href="../index.php" class="opzione-btn">TORNA</a>
                </div>
            </form>
        </section>
    </main>
<script>
// Tastiera form login (issue, variante A da issue come categorie): focus-target USER ID/PASSWORD,
// click accoda char e rimette focus, CANC = slice ultimo char.
(function()
{
    var f = document.getElementById('form-login');
    if (!f) return;
    var campi = f.querySelectorAll('input[name=id],input[name=pwd]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kb-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            target.value += b.getAttribute('data-kb-ch'); target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kb-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var minusc = false;
    var shiftBtn = f.querySelector('[data-kb-shift]');
    if (shiftBtn) shiftBtn.addEventListener('click', function()
    {
        minusc = !minusc;
        Array.prototype.forEach.call(f.querySelectorAll('[data-kb-ch]'), function(b)
        {
            var ch = b.getAttribute('data-kb-ch');
            if (ch.length === 1 && /[A-Za-z]/.test(ch))
            {
                var nc = minusc ? ch.toLowerCase() : ch.toUpperCase();
                b.setAttribute('data-kb-ch', nc); b.textContent = nc;
            }
        });
        if (target) target.focus();
    });
})();
</script>
</body>
</html>
