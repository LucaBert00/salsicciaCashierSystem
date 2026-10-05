<?php

declare(strict_types=1);

// Funzioni globali stateless della cassa (F4.1 #98,
// docs/ARCHITETTURA_REVISTA.md §10 punto 14 + §6/§7): csrf_token/csrf_ok/csrf_field
// verbatim da funzioni.inc:9-29. Restano funzioni (toccano superglobali, nessuno
// stato), mai classi. Caricato via autoload.files; guardie pluggable come env.inc:
// vince chi carica per primo, funzioni.inc conserva gli originali fino a F4.4.
// Zero side-effect a top-level: solo definizioni.
if (!function_exists('csrf_token')) {
    // Token anti-CSRF in sessione: mutazioni solo via POST con hidden tok.
    function csrf_token()
    {
        salsiccia_session_start();
        if (empty($_SESSION['tok'])) {
            $_SESSION['tok'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['tok'];
    }
}

if (!function_exists('csrf_ok')) {
    function csrf_ok()
    {
        salsiccia_session_start();
        return isset($_POST['tok'], $_SESSION['tok']) && hash_equals((string)$_SESSION['tok'], (string)$_POST['tok']);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        echo '<input type="hidden" name="tok" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
