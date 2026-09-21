<?php

declare(strict_types=1);

namespace Salsiccia\Backoffice\Tabs;

use Salsiccia\Backoffice\VisualizzaStore;

require_once __DIR__ . '/../VisualizzaStore.php';

// Controller del tab PRODOTTI (T20, verbatim da public/reserved/visualizza.php).
// Tab di fallback: il router lo usa anche quando ?tab= e' assente o invalido.
// Il router verifica csrf_ok() prima di salva; elimina lo verifica da se'.
final class ProdottiTab
{
    // Config lista: con colonna barcode tutto come oggi, senza colonna
    // select/search/orders perdono ogni riferimento barcode (#47, da e6559f4).
    // G3: senza la chiave 'barcode' resolveOrderBy non puo mai ordinarci sopra.
    public static function config(bool $haBarcode = true): array
    {
        $cfg = array('label' => 'PRODOTTI', 'titolo' => 'GESTIONE PRODOTTI',
            'from' => '`prodotti`',
            'select' => '`id_prodotto`, `descrizione_prod`, `prezzo`, `olpp`, `barcode`, `testo_biglietto`',
            'cnt' => 'COUNT(*)',
            'orders' => array('descrizione_prod' => '`descrizione_prod`', 'prezzo' => '`prezzo`', 'olpp' => '`olpp`', 'barcode' => '`barcode`'),
            'default' => '`descrizione_prod` ASC',
            'search' => array('`descrizione_prod`', '`testo_biglietto`', '`barcode`'),
            'headers' => array(array('descrizione_prod', 'DESCRIZIONE'), array('prezzo', 'PREZZO'), array('olpp', 'ETICH')));
        if (!$haBarcode) {
            $cfg['select'] = '`id_prodotto`, `descrizione_prod`, `prezzo`, `olpp`, `testo_biglietto`';
            $cfg['search'] = array('`descrizione_prod`', '`testo_biglietto`');
            unset($cfg['orders']['barcode']);
        }
        return $cfg;
    }

    // Elimina prodotto solo se non citato in posizioni, regole e righe ordini.
    // Ritorna array('redirect' => tab) | array('msg' => ...) | array('none' => true).
    public static function elimina($db, string $method, array $post, array $get): array
    {
        if ($method === 'POST' && isset($post['del'])) {
            if (!\csrf_ok()) {
                return array('msg' => 'TOKEN NON VALIDO');
            }
            $idProdotto = (int)$post['del'];
            $numRiferimenti = VisualizzaStore::contaRighe($db, "SELECT COUNT(*) AS c FROM `prodotti_categorie` WHERE id_prodotto = ?", 'i', array($idProdotto))
                + VisualizzaStore::contaRighe($db, "SELECT COUNT(*) AS c FROM `prodotti_contatori` WHERE id_prodotto = ?", 'i', array($idProdotto))
                + VisualizzaStore::contaRighe($db, "SELECT COUNT(*) AS c FROM `righe_ordini` WHERE id_prodotto = ?", 'i', array($idProdotto));
            if ($numRiferimenti > 0) {
                return array('msg' => 'PRODOTTO REFERENZIATO — ELIMINA BLOCCATA');
            }
            $stmt = $db->prepare("DELETE FROM `prodotti` WHERE id_prodotto = ?");
            $stmt->bind_param('i', $idProdotto);
            $stmt->execute();
            return array('redirect' => 'prodotti');
        }
        if (isset($get['del'])) {
            return array('msg' => 'AZIONE NON VALIDA');
        }
        return array('none' => true);
    }

    // Riga prodotto per edit, null se assente o senza chiave.
    // Senza colonna barcode: SELECT senza e chiave default '' (#47, da e6559f4).
    public static function caricaModifica($db, array $get, bool $haBarcode = true): ?array
    {
        if (!isset($get['edit'])) {
            return null;
        }
        $idProdotto = (int)$get['edit'];
        if ($haBarcode) {
            $stmt = $db->prepare("SELECT id_prodotto, descrizione_prod, prezzo, iva, testo_biglietto, olpp, barcode FROM `prodotti` WHERE id_prodotto = ?");
        } else {
            $stmt = $db->prepare("SELECT id_prodotto, descrizione_prod, prezzo, iva, testo_biglietto, olpp FROM `prodotti` WHERE id_prodotto = ?");
        }
        $stmt->bind_param('i', $idProdotto);
        $stmt->execute();
        $riga = $stmt->get_result()->fetch_assoc();
        if ($riga && !isset($riga['barcode'])) {
            $riga['barcode'] = '';
        }
        return $riga ? $riga : null;
    }

    // Salva prodotto: solo descrizione obbligatoria, barcode sempre facoltativo
    // (puo mancare il valore o l'intera colonna nei DB storici). Iva fissa 0.22,
    // default '-' a fiera spenta solo se la colonna esiste (#47, da e6559f4).
    // Ritorna array('redirect' => tab) o array('msg' => ...) senza 'riga'
    // (il router conserva la riga in modifica come l'originale).
    public static function salva($db, array $post, bool $haBarcode = true): array
    {
        $idProdotto = (int)$post['id'];
        $descrizione = substr(trim($post['descrizione_prod']), 0, 100);
        $prezzoRaw = trim($post['prezzo'] ?? '');
        $iva = 0.22;
        $testoBiglietto = substr(trim($post['testo_biglietto']), 0, 100);
        $olpp = $post['olpp'] == 'T' ? 'T' : 'F';
        $fiera = VisualizzaStore::fieraAttiva();
        if (!$haBarcode) {
            $barcode = '';
        } elseif (!$fiera && $idProdotto === 0 && trim($post['barcode'] ?? '') === '') {
            $barcode = '-';
        } else {
            $barcode = substr(trim($post['barcode'] ?? ''), 0, 20);
        }
        if ($descrizione === '') {
            return array('msg' => 'DESCRIZIONE OBBLIGATORIA');
        }
        if (!is_numeric($prezzoRaw) || (float)$prezzoRaw < 0) {
            return array('msg' => 'PREZZO NON VALIDO');
        }
        $prezzo = (float)$prezzoRaw;
        if ($idProdotto > 0) {
            if ($haBarcode) {
                $stmt = $db->prepare("UPDATE `prodotti` SET descrizione_prod = ?, prezzo = ?, iva = ?, testo_biglietto = ?, olpp = ?, barcode = ? WHERE id_prodotto = ?");
                $stmt->bind_param('sddsssi', $descrizione, $prezzo, $iva, $testoBiglietto, $olpp, $barcode, $idProdotto);
            } else {
                $stmt = $db->prepare("UPDATE `prodotti` SET descrizione_prod = ?, prezzo = ?, iva = ?, testo_biglietto = ?, olpp = ? WHERE id_prodotto = ?");
                $stmt->bind_param('sddssi', $descrizione, $prezzo, $iva, $testoBiglietto, $olpp, $idProdotto);
            }
            $stmt->execute();
        } else {
            // id AUTO_INCREMENT, niente MAX+1 (race su MyISAM senza lock).
            if ($haBarcode) {
                $stmt = $db->prepare("INSERT INTO `prodotti` (descrizione_prod, prezzo, iva, testo_biglietto, olpp, barcode) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sddsss', $descrizione, $prezzo, $iva, $testoBiglietto, $olpp, $barcode);
            } else {
                $stmt = $db->prepare("INSERT INTO `prodotti` (descrizione_prod, prezzo, iva, testo_biglietto, olpp) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sddss', $descrizione, $prezzo, $iva, $testoBiglietto, $olpp);
            }
            $stmt->execute();
        }
        return array('redirect' => 'prodotti');
    }
}
