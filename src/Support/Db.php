<?php

declare(strict_types=1);

namespace Salsiccia\Support;

// Livello accesso-dati unico della cassa (F4.1 #98,
// docs/ARCHITETTURA_REVISTA.md §10 punto 14 + §6/§9): connect verbatim da
// dbConnect.php, query/select/exec verbatim legacy pre-F4.4 (prepared
// con "?", mai interpolazione, T09 invariato). Solo metodi statici;
// i globali restano in helpers.php via autoload (F4.4 #101),
// qui la casa stabile che F4.2 adotta.
final class Db
{
    // dbConnect.php verbatim: T10 fail-closed, nessun fallback credenziali.
    // Recovery: copia .env.example in .env.
    public static function connect()
    {
        $dbName = trim((string)getenv('SALSICCIA_DB_NAME'));
        $dbHost = trim((string)getenv('SALSICCIA_DB_HOST'));
        $dbUser = trim((string)getenv('SALSICCIA_DB_USER'));
        $dbPassword = (string)getenv('SALSICCIA_DB_PASS');
        if ($dbHost === '' || $dbName === '' || $dbUser === '' || $dbPassword === '') {
            http_response_code(500);
            die('Configurazione mancante: SALSICCIA_DB_HOST/NAME/USER/PASS non impostate. Copia .env.example in .env e compila i valori.');
        }

        $mysqli = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName)
        or die(mysqli_connect_error());

        return $mysqli;
    }

    /**
     * Esegue SQL grezzo senza alcun escaping/placeholder: NON sanifica, interpola
     * cio' che riceve. T09: usare solo per query statiche senza variabili;
     * ogni variabile va in Db::select()/Db::exec() con placeholder "?".
     */
    public static function query($mysqli, $query)
    {
        // F6.3 #111: flag ex set.inc da CassaConfig (stesso valore, mai define()).
        $cfg = \Salsiccia\Config\CassaConfig::carica();
        if (!empty($cfg['DEBUG'])) {
            $risultato = mysqli_query($mysqli, $query)
            or die(print "<br><center class=errore>Errore di MySql con la query <br><b>" . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . "</b><br>" . htmlspecialchars(mysqli_error($mysqli), ENT_QUOTES, 'UTF-8') . "<br><br><INPUT type=\"BUTTON\" value='INDIETRO' onClick=\"javascript:history.back(1);\"></center>");
        } else {
            $risultato = mysqli_query($mysqli, $query);
        }

        return $risultato;
    }

    // T09: SELECT parametrizzata (pattern backoffice visualizza.php / Fiscale::emettiScontrino in src/Fiscale/Fiscale.php).
    // Ritorna mysqli_result o false come Db::query; solo "?" mai interpolazioni.
    public static function select($mysqli, $sql, $tipi = '', $params = array())
    {
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        if ($tipi !== '') {
            $stmt->bind_param($tipi, ...$params);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
        $res = $stmt->get_result();
        $stmt->close();
        return $res ? $res : false;
    }

    // T09: INSERT/UPDATE/DELETE parametrizzata. Ritorna bool.
    public static function exec($mysqli, $sql, $tipi = '', $params = array())
    {
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            return false;
        }
        if ($tipi !== '') {
            $stmt->bind_param($tipi, ...$params);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
