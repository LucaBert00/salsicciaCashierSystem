<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// F2.1 #88: use-case stampa (corpo odierno di mostraSchermataStampa(),
// functionsFrontend.inc:1431-1537, mappa §6 1482-1588).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 8 + §6 + §9 + §3.2e + §2 P3
// (sola lettura). Separazione chiave F2: le scritture (file di stampa,
// chiusura ordine, documento fiscale) hanno un nome, un posto e sono
// testabili. Mai HTML di layout qui: per il layout chiama StampaView.
// Riuso as-is (mai toccati oltre riuso, sono F4/F5): cassaCorrente(),
// db_select()/db_exec() (gia parametrizzati T09), PrintService::
// generaFileStampa(), Fiscale::emettiScontrino() (fiscale non bloccante con
// fallback + coda invariato, mai fatal su Throwable).
final class StampaController
{
    /**
     * @param mixed $db mysqli reale o doppio di test con la stessa superficie
     */
    public static function stampa($db): void
    {
        $id_cassa = cassaCorrente();

        // Errore stampante (#102): ordine ancora aperto (chiuso=0), stesso layout
        // resto via StampaView. RIPROVA rilancia la stampa una sola volta;
        // i click su banconote/monete restano qui e non ristampano mai.
        if (isset($_GET['stampa_err']))
        {
            $id_ordine_err = (int)$_GET['stampa_err'];
            $totale_err = 0;
            $risErr = db_select($db, "SELECT totale FROM ordini WHERE id_ordine = ? AND id_cassa = ? AND chiuso = '0'", 'ii', array($id_ordine_err, $id_cassa));
            if ($risErr && ($rigaErr = mysqli_fetch_array($risErr)))
                $totale_err = (float)$rigaErr['totale'];
            else
            {
                echo '<div style="text-align:center;margin-top:60px;">';
                echo '<p style="font-size:24px;font-weight:800;color:#8c9ba5;margin-bottom:40px;">NESSUN ORDINE DA RIPROVARE</p>';
                echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:20px 40px;display:inline-block;">TORNA</a>';
                echo '</div>';
                return;
            }
            $pagato_err = isset($_GET['pagato']) ? (float)$_GET['pagato'] : 0;
            // T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato).
            $pm_err = isset($_GET['metodo']) ? \Salsiccia\Cassa\PayMethod::tryFrom((string)$_GET['metodo']) : null;
            $metodo_err = $pm_err ? $pm_err->value : '';
            $base_err = '?action=s&stampa_err=' . $id_ordine_err;
            echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:6px;border-radius:6px;">STAMPANTE NON RAGGIUNGIBILE, ordine NON stampato e NON chiuso: controlla la stampante e riprova.</p>';
            StampaView::resto($totale_err, $pagato_err, $metodo_err, $base_err, false);
            echo '<div style="text-align:center;margin-top:8px;">';
            echo '<a href="index.php?action=s" class="opzione-btn" style="text-decoration:none;padding:16px 40px;display:inline-block;">RIPROVA STAMPA</a> ';
            echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:16px 40px;display:inline-block;">TORNA</a> ';
            echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:16px 40px;display:inline-block;">NUOVO ORDINE</a>';
            echo '</div>';
            return;
        }

        // Stampa una sola volta all'ingresso (?action=s senza &stampato=), poi JS-redirect
        // con &stampato= per la UI resto: evita ristampe sui click di banconote/monete.
        if (!isset($_GET['stampato']))
        {
            $ris = db_select($db, "SELECT COALESCE(NULLIF(TRIM(prodotti.testo_biglietto), ''), prodotti.descrizione_prod) AS testo, prodotti.olpp AS olpp, righe_ordini.quantita, righe_ordini.totale, ordini.totale as tot_ord, ordini.n_pezzi, ordini.id_ordine, ordini.tipo, prodotti.id_prodotto AS id_prodotto FROM prodotti, ordini, righe_ordini WHERE ordini.id_ordine = righe_ordini.id_ordine AND prodotti.id_prodotto = righe_ordini.id_prodotto AND ordini.chiuso = '0' AND ordini.id_cassa = ?", 'i', array($id_cassa));
            $numero_righe = $ris ? mysqli_num_rows($ris) : 0;

            if ($numero_righe == 0)
            {
                echo '<div style="text-align:center;margin-top:60px;">';
                echo '<p style="font-size:24px;font-weight:800;color:#8c9ba5;margin-bottom:40px;">NESSUN BIGLIETTO DA STAMPARE</p>';
                echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;padding:20px 40px;display:inline-block;">NUOVO ORDINE</a>';
                echo '</div>';
                return;
            }

            $ret_val = \Salsiccia\Cassa\PrintService::generaFileStampa($ris, $numero_righe);
            if ($ret_val === false)
                return; // errore configurazione stampante, messaggio gia' mostrato

            // Invio alla stampante fallito: l'ordine resta aperto (chiuso=0) cosi'
            // la cassa puo' riprovare; chiuderlo lo marcherebbe come stampato.
            // Redirect al ramo stampa_err: stesso layout resto, nessuna ristampa
            // sui click di banconote/monete (come il ramo stampato sotto).
            if (empty($ret_val['inviato']))
            {
                $qs = '?action=s&stampa_err=' . (int)$ret_val['id_ordine'];
                echo '<script>window.location.href=\'' . $qs . '\';</script>';
                return;
            }

            $id_ordine = (int)$ret_val['id_ordine'];
            $num_pezzi = (int)$ret_val['num_pezzi'];
            $tot_ordine = (float)$ret_val['totale'];
            db_exec($db, "UPDATE ordini SET chiuso = 1, num_biglietti = ? WHERE id_ordine = ?", 'ii', array($num_pezzi, $id_ordine));
            $qs = '?action=s&stampato=' . $id_ordine . '&tot=' . $tot_ordine;
            echo '<script>window.location.href=\'' . $qs . '\';</script>';
            return;
        }

        $id_ordine = (int)$_GET['stampato'];
        // Totale sempre dal DB via stampato, mai dal GET (display resto non fidato).
        $totale = 0;
        $risTot = db_select($db, "SELECT totale FROM ordini WHERE id_ordine = ?", 'i', array($id_ordine));
        if ($risTot && ($rigaTot = mysqli_fetch_array($risTot)))
            $totale = (float)$rigaTot['totale'];
        $pagato = isset($_GET['pagato']) ? (float)$_GET['pagato'] : 0;
        // T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato).
        $pm = isset($_GET['metodo']) ? \Salsiccia\Cassa\PayMethod::tryFrom((string)$_GET['metodo']) : null;
        $metodo = $pm ? $pm->value : '';
        $base_url = '?action=s&stampato=' . $id_ordine . '&tot=' . $totale;

        // Scontrino fiscale non bloccante (#92): al primo metodo scelto emette verso il
        // registratore o accoda; la schermata resto si mostra sempre e comunque.
        if ($metodo !== '')
        {
            try
            {
                $fisc = \Salsiccia\Fiscale\Fiscale::emettiScontrino($db, $id_ordine, $metodo);
                if (empty($fisc['ok']) && !empty($fisc['fallback']))
                    echo '<p style="background:#f0ad4e;color:#fff;font-weight:800;text-align:center;padding:10px;border-radius:6px;">REGISTRATORE NON RISPONDE: scontrino in coda, la vendita prosegue.</p>';
            }
            catch (\Throwable $e)
            {
                cassa_log('warning', 'fiscale non bloccante: ' . $e->getMessage());
            }
        }

        StampaView::resto($totale, $pagato, $metodo, $base_url);
    }
}
