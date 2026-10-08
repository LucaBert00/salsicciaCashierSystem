<?php

declare(strict_types=1);

// Funzioni globali stateless della cassa (F4.1 #98,
// docs/ARCHITETTURA_REVISTA.md §10 punto 14 + §6/§7): csrf_token/csrf_ok/csrf_field
// verbatim legacy pre-F4.4. Restano funzioni (toccano superglobali, nessuno
// stato), mai classi. Caricato via autoload.files.
// F5.5b #118: avvio sessione via Session::start() invece del globale
// salsiccia_session_start() di env.inc, cosi' csrf_* non dipende dallo shim.
// Zero side-effect a top-level: solo definizioni.
if (!function_exists('csrf_token')) {
    // Token anti-CSRF in sessione: mutazioni solo via POST con hidden tok.
    function csrf_token()
    {
        \Salsiccia\Support\Session::start();
        if (empty($_SESSION['tok'])) {
            $_SESSION['tok'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['tok'];
    }
}

if (!function_exists('csrf_ok')) {
    function csrf_ok()
    {
        \Salsiccia\Support\Session::start();
        return isset($_POST['tok'], $_SESSION['tok']) && hash_equals((string)$_SESSION['tok'], (string)$_POST['tok']);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        echo '<input type="hidden" name="tok" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

// F4.4 #101 (punto 17): nuova sede via autoload dei 3 globali DB (verbatim
// legacy pre-F4.4, logica in src/Support/Db.php da F4.1 #98). Thin wrappers verso
// Db::query/select/exec, stesse firme T09: i 51 call-site restano invariati e
// funzionano con solo autoload, zero doppie sedi dopo il delete.
if (!function_exists('mysql_query_safe')) {
    function mysql_query_safe($mysqli, $query)
    {
        return \Salsiccia\Support\Db::query($mysqli, $query);
    }
}

if (!function_exists('db_select')) {
    function db_select($mysqli, $sql, $tipi = '', $params = array())
    {
        return \Salsiccia\Support\Db::select($mysqli, $sql, $tipi, $params);
    }
}

if (!function_exists('db_exec')) {
    function db_exec($mysqli, $sql, $tipi = '', $params = array())
    {
        return \Salsiccia\Support\Db::exec($mysqli, $sql, $tipi, $params);
    }
}
