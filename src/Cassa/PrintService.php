<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// Unica casa dell'invio Ricevuta (etichetta, mai Scontrino fiscale):
// genera il file di stampa e lo invia alla Zebra (DIRETTA via lpr o RETE via FTP).
// T32 follow-up #43: corpo verbatim legacy pre-F4.4 (genera_file_stampa,
// invia_file_stampa, ftpPut). I 5 builder puri vivono in src/Cassa/LabelBuilder.php
// (F4.3 #100, funzioni §7); qui solo spool su LABELS_FILE + invio, stessi esiti.
require_once __DIR__ . '/LabelBuilder.php';

final class PrintService
{
    public static function generaFileStampa($ris, $numero_righe)
    {
        $print_order_id = PRINT_ORDER_ID;
        // F6.2 #110: evento solo via JSON (CassaFlags::festaLeggi), mai piu EVENT_NAME da set.inc.
        $festa = \Salsiccia\Config\CassaFlags::festaLeggi();
        $evento = isset($festa['event_name']) ? (string)$festa['event_name'] : '';
        $credits = CREDITS;
        $cassa = function_exists('cassaCorrente') ? cassaCorrente() : ID_CASSA;


        // Unica chiave linguaggio: "ZPL" o "EPL" da PRINTER_LANGUAGE.
        $tipo_stampante = PRINTER_LANGUAGE;

        // Gate unico PrinterRegistry: stessa tabella di CONTINUOUS_LABEL in set.inc.
        $stampa_permessa = \Salsiccia\Printer\PrinterRegistry::gateAllowed(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE);

        if ($stampa_permessa == false) {
            $specifiche = "";
            if (PRINTER_NAME == "GX420t") {
                $specifiche = "supporta ZPL e EPL, ma lavora solo in modalità DIRETTA.";
            } elseif (PRINTER_NAME == "ZD230") {
                $specifiche = "lavora in DIRETTA e RETE, ma supporta solo il linguaggio ZPL.";
            } elseif (PRINTER_NAME == "TLP2844" || PRINTER_NAME == "LP2844") {
                $specifiche = "supporta solo il linguaggio EPL e solo in modalità DIRETTA.";
            }

            print "<div style='background-color: #d9534f; color: white; padding: 20px; font-family: \"Trebuchet MS\"; font-weight: bold; text-align: center; border-radius: 5px; margin: 20px 0;'>";
            print " ERRORE DI CONFIGURAZIONE <br>";

            // Ecco la tua riga modificata che concatena la variabile dinamica:
            print "<span style='font-size: 14px; font-weight: normal;'>La stampante <b>" . PRINTER_NAME . "</b> non è compatibile con la modalità <b>" . PRINTER_CONNECTION . "</b> in linguaggio <b>" . PRINTER_LANGUAGE . "</b>. Impostazioni bloccate.<br> Questa stampante " . $specifiche . "</span>";

            print "</div>";
            print "<center><button class=\"bottone_conf\" onclick=\"location.href='index.php'\"><b>TORNA AL MENU</b></button></center>";

            return false;
        }

        #apertura file
        $pf = fopen(LABELS_FILE, "w") or die("Impossibile aprire il file di stampa");

        if (CONTINUOUS_LABEL) {
            $righe = array();
            while ($riga_tmp = mysqli_fetch_array($ris)) {
                $righe[] = $riga_tmp;
            }
            $id_ordine = $righe[0]['id_ordine'];
            $totale = number_format((float)$righe[0]['tot_ord'], 2, '.', '.');
            $now = date("d/m/y H:i");
            $ret_c = \Salsiccia\Cassa\etichetta_continua($righe, $numero_righe, $evento, $credits, $cassa, $now, $tipo_stampante);
            $label = $ret_c['label'];

            $num_pezzi = 1;
        } else {
            // BIGLIETTI SINGOLI (Passaggio parametri a get_product_label)

            $riga_biglietto = mysqli_fetch_array($ris);
            $id_ordine = $riga_biglietto['id_ordine'];
            $totale = number_format((float)$riga_biglietto['tot_ord'], 2, '.', '.');
            $tipo = $riga_biglietto['tipo'];
            $t = "";

            if ($print_order_id == 1) {
                $id = "N.$id_ordine";
            } else {
                $id = "";
            }

            if ($tipo == "pre") {
                $t = "#P#";
            }
            if ($tipo == "mus") {
                $t = "#M#";
            }
            if ($tipo == "stf") {
                $t = "#S#";
            }
            if ($tipo == "asp") {
                $t = "#A#";
            }

            mysqli_data_seek($ris, 0);
            $label = "";

            # Singola: mai biglietto totale ne' "Arrivederci e grazie" (solo ramo continuo).
            # Ogni etichetta ha solo il prodotto (ramo totale mai emesso qui).
            $label = "";
            $num_pezzi = 0;

            while ($riga = mysqli_fetch_array($ris)) {
                $now = date("d/m/y H:i");
                $testo = \Salsiccia\Cassa\testo_biglietti($riga['testo']);
                $quantita = $riga['quantita'];
                $olpp_2 = $riga['olpp'];

                // Compilazione dei parametri da passare
                $parametri["now"] = $now;
                $parametri["id"] = $id;
                $parametri["cassa"] = $cassa;
                $parametri["credits"] = $credits;
                $parametri["evento"] = $evento;
                $parametri["t"] = $t;
                $parametri["quantita"] = $quantita;
                $parametri["testo"] = $testo;
                $parametri["olpp"] = $olpp_2;
                $parametri["num_pezzi"] = $num_pezzi;
                $parametri["print_order_id"] = $print_order_id;

                // PASSA LA COSTANTE DI LINGUAGGIO DINAMICA
                $parametri["tipo_stampante"] = $tipo_stampante;

                // F6.2 #110: ramo menu eliminato con la costante morta (sempre falso:
                // ogni etichetta ha solo il prodotto via get_product_label).
                $ret = \Salsiccia\Cassa\get_product_label($parametri);
                $num_pezzi = $ret['num_pezzi'];
                $label .= $ret['label'];
            }
        }

        #Scrittura finale del file unico generato
        // Taglio GX420t a richiesta: EPL = C (fuori form), ZPL = ^MMC (dentro ^XA).
        $vuole_taglio = isset($_GET['taglia']) && $_GET['taglia'] === '1' && CONTINUOUS_LABEL && \Salsiccia\Printer\PrinterRegistry::taglioPermesso(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE);
        if ($vuole_taglio && PRINTER_LANGUAGE === 'ZPL')
            $label = preg_replace('/\^XA\s*/', "^XA\n^MMC\n", $label, 1);
        elseif ($vuole_taglio && PRINTER_LANGUAGE === 'EPL')
            $label .= "C\n";
        else
            $label .= " \n";
        fwrite($pf, $label, strlen($label));
        fclose($pf);

        #L'invio in stampante avviene in entrambe le modalita (continua e biglietti singoli)
        $vect["inviato"] = self::inviaFileStampa();

        $vect["num_pezzi"] = $num_pezzi;
        $vect["id_ordine"] = $id_ordine;
        $vect["totale"] = $totale;
        return $vect;
    }

    public static function inviaFileStampa()
    {
        // nessun input GET/POST qui, solo costanti set.inc; whitelist+escapeshellarg+error_log.
        // T16: LABELS_FILE e' assoluto in storage/, whitelist sul basename.
        if (!preg_match('/^[A-Za-z0-9_-]+$/', PRINTER_NAME) || !preg_match('/^[A-Za-z0-9_.-]+$/', basename(LABELS_FILE))) {
            \Salsiccia\Support\Env::log('warning', "invia_file_stampa bloccato: costanti stampa non whitelistate");
            return false;
        }
        if (PRINTER_CONNECTION == "DIRETTA") {
            $coda = \Salsiccia\Printer\CupsState::cupsQueue(PRINTER_NAME);
            $cmd = "lpr -P " . escapeshellarg($coda) . " " . escapeshellarg(LABELS_FILE);
            system($cmd, $rc);
            if ($rc !== 0) {
                \Salsiccia\Support\Env::log('error', "lpr fallito rc=$rc printer=" . $coda . " file=" . LABELS_FILE);
            }
            return $rc === 0;
        }
        return self::ftpPut();
    }

    // F3.2 #62: invio setup carta (contenuto normalizzato da PaperSetup), stessa
    // logica di invio setup upstream (a79ed89): valida -> tmp
    // -> DIRETTA/RETE -> unlink -> return. Mai LABELS_FILE come remoto
    // (nel target e' un path storage, set.inc:43): solo basename.
    public static function inviaSetupCarta(string $contenuto): bool
    {
        if ($contenuto === '' || strlen($contenuto) > 8192 || strpos($contenuto, "\0") !== false) {
            return false;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'carta');
        if ($tmp === false) {
            \Salsiccia\Support\Env::log('error', 'carta_stampa: tmp non creato');
            return false;
        }
        if (file_put_contents($tmp, $contenuto, LOCK_EX) === false) {
            \Salsiccia\Support\Env::log('error', 'carta_stampa: scrittura tmp fallita');
            @unlink($tmp);
            return false;
        }
        if (PRINTER_CONNECTION == 'DIRETTA') {
            $coda = \Salsiccia\Printer\CupsState::cupsQueue(PRINTER_NAME);
            $cmd = 'lpr -P ' . escapeshellarg($coda) . ' ' . escapeshellarg($tmp);
            system($cmd, $rc);
            @unlink($tmp);
            if ($rc !== 0) {
                \Salsiccia\Support\Env::log('error', 'carta_stampa: lpr fallito rc=' . $rc . ' printer=' . $coda);
            }
            return $rc === 0;
        }
        $ftp = ftp_connect(PRINTER_IP, 21, 5);
        if ($ftp === false) {
            \Salsiccia\Support\Env::log('error', 'carta_stampa: ftp_connect fallito verso ' . PRINTER_IP);
            @unlink($tmp);
            return false;
        }
        if (!ftp_login($ftp, '', '')) {
            \Salsiccia\Support\Env::log('error', 'carta_stampa: ftp_login anonimo fallito verso ' . PRINTER_IP);
            ftp_close($ftp);
            @unlink($tmp);
            return false;
        }
        $ok = ftp_put($ftp, basename(LABELS_FILE), $tmp, FTP_BINARY);
        if (!$ok) {
            \Salsiccia\Support\Env::log('error', 'carta_stampa: ftp_put fallito verso ' . PRINTER_IP);
        }
        ftp_close($ftp);
        @unlink($tmp);
        return $ok;
    }

    public static function ftpPut()
    {
        $file = LABELS_FILE;

        // set up basic connection
        // Il print server Zebra accetta solo FTP anonimo, senza credenziali;
        // timeout corto cosi' in fiera la cassa fallisce in fretta invece di bloccarsi.
        $ftp = ftp_connect(PRINTER_IP, 21, 5);
        if ($ftp === false) {
            \Salsiccia\Support\Env::log('error', "ftp_connect fallito verso " . PRINTER_IP);
            return false;
        }

        // login with username and password
        if (!ftp_login($ftp, "", "")) {
            \Salsiccia\Support\Env::log('error', "ftp_login anonimo fallito verso " . PRINTER_IP);
            ftp_close($ftp);
            return false;
        }

        // upload a file (spool locale assoluto in storage/ da T16, nome remoto solo basename)
        $ok = ftp_put($ftp, basename($file), $file, FTP_BINARY);
        if (!$ok) {
            \Salsiccia\Support\Env::log('error', "ftp_put fallito file=$file verso " . PRINTER_IP);
        }

        // close the connection
        ftp_close($ftp);
        return $ok;
    }
}
