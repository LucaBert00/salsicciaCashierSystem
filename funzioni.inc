<?php
declare(strict_types=1);
// T31: file canonico (non shim) — migrazione per-funzione verso src/ (v. docs/ARCHITETTURA_REVISTA.md §6 e §9, fase 4): genera/invia/ftp stampa estratti in src/Cassa/PrintService.php (#43); rimozione solo dopo lo split, rivalutare entro 2026-12-31.
if (!function_exists('cassa_log'))
    require_once __DIR__ . '/env.inc';
require_once __DIR__ . '/src/Cassa/LabelBuilder.php';
//define("DEBUG", 1);

// Token anti-CSRF in sessione: mutazioni solo via POST con hidden tok.
// F4.1 #98: guardia pluggable (come env.inc) — la copia canonica vive in
// src/Support/helpers.php via autoload.files; vince chi carica per primo.
// Corpi intoccati, rimozione rinviata a F4.4.
if (!function_exists('csrf_token'))
{
function csrf_token()
{
    salsiccia_session_start();
    if (empty($_SESSION['tok']))
        $_SESSION['tok'] = bin2hex(random_bytes(32));
    return $_SESSION['tok'];
}
}

if (!function_exists('csrf_ok'))
{
function csrf_ok()
{
    salsiccia_session_start();
    return isset($_POST['tok'], $_SESSION['tok']) && hash_equals((string)$_SESSION['tok'], (string)$_POST['tok']);
}
}

if (!function_exists('csrf_field'))
{
function csrf_field()
{
    echo '<input type="hidden" name="tok" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}
}


/**
 * Esegue SQL grezzo senza alcun escaping/placeholder: NON sanifica, interpola
 * cio' che riceve. T09: usare solo per query statiche senza variabili;
 * ogni variabile va in db_select()/db_exec() con placeholder "?".
 */
function mysql_query_safe($mysqli, $query)
{
    if(defined('DEBUG') && DEBUG)
    {
        $risultato = mysqli_query($mysqli, $query)
        or die(print "<br><center class=errore>Errore di MySql con la query <br><b>" . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . "</b><br>" . htmlspecialchars(mysqli_error($mysqli), ENT_QUOTES, 'UTF-8') . "<br><br><INPUT type=\"BUTTON\" value='INDIETRO' onClick=\"javascript:history.back(1);\"></center>");
    }
    else
    {
        $risultato = mysqli_query($mysqli, $query);
    }

    return $risultato;
}

// T09: SELECT parametrizzata (pattern backoffice visualizza.php / Fiscale::emettiScontrino in src/Fiscale/Fiscale.php).
// Ritorna mysqli_result o false come mysql_query_safe; solo "?" mai interpolazioni.
function db_select($mysqli, $sql, $tipi = '', $params = array())
{
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false)
        return false;
    if ($tipi !== '')
        $stmt->bind_param($tipi, ...$params);
    if (!$stmt->execute())
    {
        $stmt->close();
        return false;
    }
    $res = $stmt->get_result();
    $stmt->close();
    return $res ? $res : false;
}

// T09: INSERT/UPDATE/DELETE parametrizzata. Ritorna bool.
function db_exec($mysqli, $sql, $tipi = '', $params = array())
{
    $stmt = $mysqli->prepare($sql);
    if ($stmt === false)
        return false;
    if ($tipi !== '')
        $stmt->bind_param($tipi, ...$params);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// F4.2 #99: alias sottile verso OrderService::calcolaTotali() (unica sede,
// src/Cassa/OrderService.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('calcolaTotali'))
{
function calcolaTotali(int $id_prodotto, int $id_ordine, mysqli $mysqli, bool $in_txn = false): bool
{
    return (new \Salsiccia\Cassa\OrderService($mysqli, 0))->calcolaTotali($id_prodotto, $id_ordine, $in_txn);
}
}

// F4.3 #100: alias sottile verso Salsiccia\Cassa\testo_biglietti() (unica sede,
// src/Cassa/LabelBuilder.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('testo_biglietti'))
{
function testo_biglietti($stringa)
{
    return \Salsiccia\Cassa\testo_biglietti($stringa);
}
}


// F4.3 #100: alias sottile verso Salsiccia\Cassa\stampaMenu() (unica sede,
// src/Cassa/LabelBuilder.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('stampaMenu'))
{
function stampaMenu($testo,$quantita,$tipo_stampa,$parametri, $id=0)
{
    return \Salsiccia\Cassa\stampaMenu($testo, $quantita, $tipo_stampa, $parametri, $id);
}
}


// F4.2 #99: alias sottile verso StatsData::contatori_totali() (unica sede,
// src/Stats/StatsData.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('contatori_totali'))
{
function contatori_totali($mysqli)
{
    return \Salsiccia\Stats\StatsData::contatori_totali($mysqli);
}
}

// F4.3 #100: alias sottile verso Salsiccia\Cassa\generaCardDegustazione() (unica
// sede, src/Cassa/LabelBuilder.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('generaCardDegustazione'))
{
function generaCardDegustazione($numero, $qta)
{
    return \Salsiccia\Cassa\generaCardDegustazione($numero, $qta);
}
}


// F4.3 #100: alias sottile verso Salsiccia\Cassa\etichetta_continua() (unica
// sede, src/Cassa/LabelBuilder.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('etichetta_continua'))
{
function etichetta_continua($righe, $numero_righe, $evento, $credits, $cassa, $now, $tipo_stampante)
{
    return \Salsiccia\Cassa\etichetta_continua($righe, $numero_righe, $evento, $credits, $cassa, $now, $tipo_stampante);
}
}


// F4.3 #100: alias sottile verso Salsiccia\Cassa\get_product_label() (unica
// sede, src/Cassa/LabelBuilder.php). Rimosso in F4.4; zero logica qui.
if (!function_exists('get_product_label'))
{
function get_product_label($parametri)
{
    return \Salsiccia\Cassa\get_product_label($parametri);
}
}





?>
