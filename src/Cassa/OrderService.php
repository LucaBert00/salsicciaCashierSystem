<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// Unica casa testabile dei flussi ordine (T18, da gestisciAzioni()).
// DB iniettato via costruttore, mai preso dallo stato globale (come il sender
// iniettabile di Fiscale::ritentaCoda() in src/Fiscale/Fiscale.php); T06-T09
// preservati verbatim: transazioni + prepared + scope id_cassa (T05).
if (!function_exists('db_select')) {
    require_once dirname(__DIR__, 2) . '/funzioni.inc';
}
require_once __DIR__ . '/OrderType.php';

final class OrderService
{
    /** @var mixed $db mysqli reale o doppio di test con la stessa superficie */
    private $db;
    private int $idCassa;

    public function __construct($db, int $idCassa)
    {
        $this->db = $db;
        $this->idCassa = $idCassa;
    }

    // action=r: annulla l'ordine live della cassa.
    public function annullaOrdine(): bool
    {
        $db = $this->db;
        $ris = db_select($db, "SELECT id_ordine FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($this->idCassa));
        if ($ris && mysqli_num_rows($ris) == 1) {
            $id_ordine = mysqli_fetch_array($ris);
            $id_ordine = (int)$id_ordine['id_ordine'];
            db_exec($db, "DELETE FROM ordini WHERE id_ordine = ? AND id_cassa = ? AND chiuso = '0'", 'ii', array($id_ordine, $this->idCassa));
            db_exec($db, "DELETE FROM righe_ordini WHERE id_ordine = ?", 'i', array($id_ordine));
            return true;
        }
        return false;
    }

    // action=a: aggiungi prodotto; ritorna id_ordine (>0) o 0 se rollback.
    public function aggiungiProdotto(int $idProdotto): int
    {
        $db = $this->db;
        $idProdotto = (int)$idProdotto;
        $id_ordine = 0;

        $ris = db_select($db, "SELECT * FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($this->idCassa));

        if ($ris && mysqli_num_rows($ris) == 1) {
            //Ordine esiste recupero id_ordine
            $id_ordine = mysqli_fetch_array($ris);
            $id_ordine = (int)$id_ordine['id_ordine'];

            $ris = db_select($db, "SELECT * FROM righe_ordini WHERE id_ordine = ? AND id_prodotto = ?", 'ii', array($id_ordine, $idProdotto));

            if ($ris && mysqli_num_rows($ris) == 1) {
                //prodotto già presente incrementa
                db_exec($db, "UPDATE righe_ordini SET quantita = quantita + 1 WHERE id_ordine = ? AND id_prodotto = ?", 'ii', array($id_ordine, $idProdotto));
            } else {
                // Nuovo prodotto inserimento riga
                $ris = db_select($db, "SELECT * FROM prodotti WHERE id_prodotto = ?", 'i', array($idProdotto));
                $prodotto = $ris ? mysqli_fetch_array($ris) : false;
                if ($prodotto) {
                    $pid = (int)$prodotto['id_prodotto'];
                    $ppz = (float)$prodotto['prezzo'];
                    db_exec($db, "INSERT INTO righe_ordini (id_ordine, id_prodotto, quantita, totale) VALUES (?, ?, '1', ?)", 'iid', array($id_ordine, $pid, $ppz));
                }
            }
        } else {
            // Nessun ordine aperto, creo uno nuovo (T06: INSERT ordini + INSERT righe atomici;
            // effettivo su InnoDB da T21, su MyISAM baseline e' no-op senza effetti collaterali).
            $db->begin_transaction();
            $nuovo_ok = false;
            try {
                $nuovo_ok = db_exec($db, "INSERT INTO ordini (data_ora, tipo, totale, n_pezzi, id_cassa, chiuso, num_biglietti) VALUES (NOW(), ?, '0', '0', ?, '0', 0)", 'si', array(OrderType::Normale->value, $this->idCassa));
                $id_ordine = $nuovo_ok ? (int)mysqli_insert_id($db) : 0;
                if ($nuovo_ok && $id_ordine > 0) {
                    $ris = db_select($db, "SELECT * FROM prodotti WHERE id_prodotto = ?", 'i', array($idProdotto));
                    $prodotto = $ris ? mysqli_fetch_array($ris) : false;
                    if ($prodotto) {
                        $pid = (int)$prodotto['id_prodotto'];
                        $ppz = (float)$prodotto['prezzo'];
                        $nuovo_ok = db_exec($db, "INSERT INTO righe_ordini (id_ordine, id_prodotto, quantita, totale) VALUES (?, ?, '1', ?)", 'iid', array($id_ordine, $pid, $ppz));
                    } else {
                        $nuovo_ok = false;
                    }
                } else {
                    $nuovo_ok = false;
                }
            } catch (\Throwable) {
                // PHP8: query fallita lancia invece di tornare false; annulla come sopra.
                $nuovo_ok = false;
            }
            if ($nuovo_ok) {
                $db->commit();
            } else {
                $db->rollback();
                cassa_log('warning', 'ordini: rollback nuovo ordine [T06]');
                $id_ordine = 0;
            }
        }

        // ricalcolo ordine (saltato se il rollback ha annullato la creazione: niente orfani)
        if ($id_ordine > 0) {
            $this->calcolaTotali($idProdotto, $id_ordine);
        }
        return $id_ordine;
    }

    // action=mq: imposta quantita + ricalcolo in un'unica transazione (T08).
    public function impostaQuantita(int $idProdotto, int $qta): bool
    {
        $db = $this->db;
        $idProdotto = (int)$idProdotto;
        $qta = (int)$qta;
        if ($qta <= 0) {
            return false;
        }
        $ris = db_select($db, "SELECT id_ordine FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($this->idCassa));
        if (!($ris && mysqli_num_rows($ris) == 1)) {
            return false;
        }
        $riga = mysqli_fetch_array($ris);
        $id_ordine = (int)$riga['id_ordine'];
        // T08: UPDATE quantita + ricalcolo totali in un'unica transazione
        // (effettiva su InnoDB da T21, no-op su MyISAM baseline); il ricalcolo
        // riusa la transazione esterna ($in_txn) per non fare implicit commit.
        $db->begin_transaction();
        $mq_ok = false;
        try {
            $mq_ok = db_exec($db, "UPDATE righe_ordini SET quantita = ? WHERE id_ordine = ? AND id_prodotto = ?", 'iii', array($qta, $id_ordine, $idProdotto));
            if ($mq_ok) {
                $mq_ok = $this->calcolaTotali($idProdotto, $id_ordine, true);
            }
        } catch (\Throwable) {
            // PHP8: query fallita lancia invece di tornare false; annulla come sopra.
            $mq_ok = false;
        }
        if ($mq_ok) {
            $db->commit();
        } else {
            $db->rollback();
            cassa_log('warning', "mq: rollback id_ordine=$id_ordine [T08]");
        }
        return (bool)$mq_ok;
    }

    // action=mr: rimuovi riga + ricalcolo in un'unica transazione (T08).
    public function rimuoviRiga(int $idProdotto): bool
    {
        $db = $this->db;
        $idProdotto = (int)$idProdotto;
        $ris = db_select($db, "SELECT id_ordine FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($this->idCassa));
        if (!($ris && mysqli_num_rows($ris) == 1)) {
            return false;
        }
        $riga = mysqli_fetch_array($ris);
        $id_ordine = (int)$riga['id_ordine'];
        // T08: DELETE riga + ricalcolo totali in un'unica transazione (come mq sopra).
        $db->begin_transaction();
        $mr_ok = false;
        try {
            $mr_ok = db_exec($db, "DELETE FROM righe_ordini WHERE id_ordine = ? AND id_prodotto = ?", 'ii', array($id_ordine, $idProdotto));
            if ($mr_ok) {
                $mr_ok = $this->calcolaTotali($idProdotto, $id_ordine, true);
            }
        } catch (\Throwable) {
            // PHP8: query fallita lancia invece di tornare false; annulla come sopra.
            $mr_ok = false;
        }
        if ($mr_ok) {
            $db->commit();
        } else {
            $db->rollback();
            cassa_log('warning', "mr: rollback id_ordine=$id_ordine [T08]");
        }
        return (bool)$mr_ok;
    }

    // F4.2 #99: unica casa dei totali (verbatim da funzioni.inc:93-175, $mysqli
    // -> $this->db, T07 invariato). Resta sui globali db_select/db_exec.
    public function calcolaTotali(int $id_prodotto, int $id_ordine, bool $in_txn = false): bool
    {
        // cast difensivo, i chiamanti cassa passano gia (int); la firma typed (T22)
        // rende ogni coercizione un TypeError forte in rehearsal, mai drift in fiera.
        $id_prodotto = (int)$id_prodotto;
        $id_ordine = (int)$id_ordine;
        // T08: $in_txn=true quando il chiamante (mq/mr) possiede gia' la transazione:
        // niente begin/commit/rollback interni (un begin annidato farebbe implicit commit
        // su InnoDB), l'esito torna al chiamante. Ritorna bool per la catena esterna.
        $ok = false;
        if(isSet($id_prodotto))
        {
            // T07: catena SELECT->UPDATE->SELECT->UPDATE in un'unica transazione; la riga
            // ordini padre e' bloccata via SELECT ... FOR UPDATE dove il motore lo permette
            // (effettivo su InnoDB da T21, no-op su MyISAM baseline). Ordine di lock costante
            // padre->figlio anti-deadlock; niente retry interno, il prossimo tap ricalcola.
            if (!$in_txn)
                $this->db->begin_transaction();
            $ok = true;
            try
            {
                #Lock riga padre: serializza i tap concorrenti sullo stesso ordine.
                $ris = db_select($this->db, "SELECT id_ordine FROM ordini WHERE id_ordine = ? FOR UPDATE", 'i', array($id_ordine));
                if (!$ris)
                    $ris = db_select($this->db, "SELECT id_ordine FROM ordini WHERE id_ordine = ?", 'i', array($id_ordine));
                $ok = (bool)$ris;
                if ($ok)
                {
                    #DOPO AVER INSERITO O AGGIUNTO UN PRODOTTO DEVO AGGIORNARE QUANTITA E TOTALE IN righe_ordini
                    $ris = db_select($this->db, "SELECT quantita, prezzo FROM prodotti,righe_ordini WHERE prodotti.id_prodotto = righe_ordini.id_prodotto AND id_ordine = ? AND prodotti.id_prodotto = ?", 'ii', array($id_ordine, $id_prodotto));
                    //print "query: calcolaTotali select righe";
                    $ok = (bool)$ris;
                }
                if($ok && mysqli_num_rows($ris) > 0)
                {
                    $riga = mysqli_fetch_array($ris);
                    $quantita = (int)$riga['quantita'];
                    $prezzo = (float)$riga['prezzo'];
                    $totale = $quantita * $prezzo;
                    //print "\n<script>alert('n_pezzi: $quantita - totale:$totale');</script>";
                    //print "query: calcolaTotali update righe";
                    $ok = db_exec($this->db, "UPDATE righe_ordini set quantita = ?, totale = ? WHERE id_ordine = ? AND id_prodotto = ?", 'idii', array($quantita, $totale, $id_ordine, $id_prodotto));
                }
                if ($ok)
                {
                    #DOPO AVER INSERITO O AGGIUNTO UN PRODOTTO DEVO AGGIORNARE n_pezzi e totale IN ordini
                    $ris = db_select($this->db, "SELECT sum(quantita) as n_pezzi, sum(totale) as totale FROM righe_ordini WHERE id_ordine = ? GROUP BY id_ordine", 'i', array($id_ordine));
                    $ok = (bool)$ris;
                }
                if ($ok)
                {
                    $riga = mysqli_fetch_array($ris);
                    $n_pezzi = (int)$riga['n_pezzi'];
                    $totale = (float)$riga['totale'];

                    if($totale == 0)
                        //print "query: calcolaTotali azzera ordini";
                        $ok = db_exec($this->db, "UPDATE ordini set n_pezzi = 0, totale = 0 WHERE id_ordine = ?", 'i', array($id_ordine));
                    //print "query: calcolaTotali update ordini";
                    else
                        $ok = db_exec($this->db, "UPDATE ordini set n_pezzi = ?, totale = ? WHERE id_ordine = ?", 'idi', array($n_pezzi, $totale, $id_ordine));
                    //print "query: calcolaTotali update ordini";
                }
            }
            catch (Throwable)
            {
                // PHP8: query fallita lancia invece di tornare false; annulla come sopra.
                $ok = false;
            }
            if ($in_txn)
                return $ok; // commit/rollback al chiamante (mq/mr, T08).
            if ($ok)
                $this->db->commit();
            else
            {
                $this->db->rollback();
                cassa_log('warning', "calcolaTotali: rollback id_ordine=$id_ordine [T07]");
            }
        }

        return $ok;

    }

    // action=st: imposta tipo ordine (T26: whitelist = OrderType enum).
    public function impostaTipo(string $tipo): bool
    {
        $db = $this->db;
        if (OrderType::tryFrom($tipo) === null) {
            return false;
        }
        $ris = db_select($db, "SELECT id_ordine FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($this->idCassa));
        if ($ris && mysqli_num_rows($ris) == 1) {
            $riga = mysqli_fetch_array($ris);
            $id_ordine = (int)$riga['id_ordine'];
            return (bool)db_exec($db, "UPDATE ordini SET tipo = ? WHERE id_ordine = ?", 'si', array($tipo, $id_ordine));
        }
        return (bool)db_exec($db, "INSERT INTO ordini (data_ora, tipo, totale, n_pezzi, id_cassa, chiuso, num_biglietti) VALUES (NOW(), ?, '0', '0', ?, '0', 0)", 'si', array($tipo, $this->idCassa));
    }

    // action=sb: ordine live in standby.
    public function mettiStandby(): bool
    {
        $db = $this->db;
        $ris = db_select($db, "SELECT id_ordine FROM ordini WHERE id_cassa = ? AND chiuso = '0'", 'i', array($this->idCassa));
        if ($ris && mysqli_num_rows($ris) == 1) {
            $riga = mysqli_fetch_array($ris);
            $id_ordine = (int)$riga['id_ordine'];
            return (bool)db_exec($db, "UPDATE ordini SET chiuso = 'S' WHERE id_ordine = ?", 'i', array($id_ordine));
        }
        return false;
    }

    // action=ra: riattiva da standby, solo stessa cassa + stato S (T05).
    public function riattiva(int $idOrdine): bool
    {
        $db = $this->db;
        $idOrdine = (int)$idOrdine;
        return (bool)db_exec($db, "UPDATE ordini SET chiuso = '0' WHERE id_ordine = ? AND id_cassa = ? AND chiuso = 'S'", 'ii', array($idOrdine, $this->idCassa));
    }
}
