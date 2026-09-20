<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// Unica casa testabile dei flussi ordine (T18, da gestisciAzioni()).
// DB iniettato via costruttore, mai preso dallo stato globale (come il sender
// iniettabile di fiscale_ritenta_coda() in fiscale.inc:153); T06-T09
// preservati verbatim: transazioni + prepared + scope id_cassa (T05).
if (!function_exists('db_select')) {
    require_once dirname(__DIR__, 2) . '/funzioni.inc';
}

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
                $nuovo_ok = db_exec($db, "INSERT INTO ordini (data_ora, tipo, totale, n_pezzi, id_cassa, chiuso, num_biglietti) VALUES (NOW(), 'nor', '0', '0', ?, '0', 0)", 'i', array($this->idCassa));
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
            calcolaTotali($idProdotto, $id_ordine, $db);
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
                $mq_ok = calcolaTotali($idProdotto, $id_ordine, $db, true);
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
                $mr_ok = calcolaTotali($idProdotto, $id_ordine, $db, true);
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

    // action=st: imposta tipo ordine (whitelist invariata).
    public function impostaTipo(string $tipo): bool
    {
        $db = $this->db;
        $validi = array('nor', 'pre', 'mus', 'stf', 'asp');
        if (!in_array($tipo, $validi)) {
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
