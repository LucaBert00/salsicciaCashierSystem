<?php

declare(strict_types=1);

namespace Salsiccia\Backoffice\Tabs;

use Salsiccia\Backoffice\VisualizzaStore;

require_once __DIR__ . '/../VisualizzaStore.php';

// Controller del tab REGOLE VENDUTI / prodotti_contatori (T20, verbatim da visualizza.php).
// Il router verifica csrf_ok() prima di salva; elimina lo verifica da se'.
final class ProdottiContatoriTab
{
    public static function config(): array
    {
        return array('label' => 'REGOLE VENDUTI', 'titolo' => 'GESTIONE REGOLE VENDUTI',
            'from' => '`prodotti_contatori` `pc` LEFT JOIN `contatori` `c` ON `c`.`id_contatore` = `pc`.`id_contatore` LEFT JOIN `prodotti` `p` ON `p`.`id_prodotto` = `pc`.`id_prodotto`',
            'select' => '`pc`.`id_contatore`, `pc`.`id_prodotto`, `pc`.`quantita`, COALESCE(`c`.`nome`, \'(ORFANO)\') AS `contatore`, COALESCE(`p`.`descrizione_prod`, \'(ORFANO)\') AS `prodotto`',
            'cnt' => 'COUNT(*)',
            'orders' => array('contatore' => '`contatore`', 'prodotto' => '`prodotto`', 'quantita' => '`pc`.`quantita`'),
            'default' => '`contatore` ASC',
            'search' => array('`c`.`nome`', '`p`.`descrizione_prod`'),
            'headers' => array(array('contatore', 'VENDUTO'), array('prodotto', 'PRODOTTO'), array('quantita', 'QTA')));
    }

    // Elimina regola su coppia venduto+prodotto.
    // Ritorna array('redirect' => tab) | array('msg' => ...) | array('none' => true).
    public static function elimina($db, string $method, array $post, array $get): array
    {
        if ($method === 'POST' && isset($post['del_c'], $post['del_p'])) {
            if (!\csrf_ok()) {
                return array('msg' => 'TOKEN NON VALIDO');
            }
            $idContatore = (int)$post['del_c'];
            $idProdotto = (int)$post['del_p'];
            $stmt = $db->prepare("DELETE FROM `prodotti_contatori` WHERE id_contatore = ? AND id_prodotto = ?");
            $stmt->bind_param('ii', $idContatore, $idProdotto);
            $stmt->execute();
            return array('redirect' => 'prodotti_contatori');
        }
        if (isset($get['del_c'], $get['del_p'])) {
            return array('msg' => 'AZIONE NON VALIDA');
        }
        return array('none' => true);
    }

    // Riga regola per edit su coppia, null se assente o incompleta.
    public static function caricaModifica($db, array $get): ?array
    {
        if (!isset($get['edit_c'], $get['edit_p'])) {
            return null;
        }
        $idContatore = (int)$get['edit_c'];
        $idProdotto = (int)$get['edit_p'];
        $stmt = $db->prepare("SELECT id_contatore, id_prodotto, quantita FROM `prodotti_contatori` WHERE id_contatore = ? AND id_prodotto = ?");
        $stmt->bind_param('ii', $idContatore, $idProdotto);
        $stmt->execute();
        $riga = $stmt->get_result()->fetch_assoc();
        return $riga ? $riga : null;
    }

    // Salva regola: dup sulla coppia bloccato.
    // Ritorna array('redirect' => tab) o array('msg' => ..., 'riga' => ...).
    public static function salva($db, array $post): array
    {
        $idContatore = (int)$post['id_contatore'];
        $idProdotto = (int)$post['id_prodotto'];
        $quantita = max(0, (int)$post['quantita']);
        $vecchioIdContatore = (int)$post['oid_c'];
        $vecchioIdProdotto = (int)$post['oid_p'];
        $inModifica = isset($post['is_edit']) && $post['is_edit'] === '1';
        $rigaInModifica = array('id_contatore' => $idContatore, 'id_prodotto' => $idProdotto, 'quantita' => $quantita);
        if ($idContatore > 0 && $idProdotto > 0) {
            $numDuplicati = VisualizzaStore::contaRighe($db, "SELECT COUNT(*) AS c FROM `prodotti_contatori` WHERE id_contatore = ? AND id_prodotto = ?", 'ii', array($idContatore, $idProdotto));
            if ($inModifica && $idContatore == $vecchioIdContatore && $idProdotto == $vecchioIdProdotto) {
                $numDuplicati = 0;
            }
            if ($numDuplicati > 0) {
                return array('msg' => 'REGOLA GIA ESISTENTE', 'riga' => $rigaInModifica);
            }
            if ($inModifica) {
                $stmt = $db->prepare("UPDATE `prodotti_contatori` SET id_contatore = ?, id_prodotto = ?, quantita = ? WHERE id_contatore = ? AND id_prodotto = ?");
                $stmt->bind_param('iiiii', $idContatore, $idProdotto, $quantita, $vecchioIdContatore, $vecchioIdProdotto);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO `prodotti_contatori` (id_contatore, id_prodotto, quantita) VALUES (?, ?, ?)");
                $stmt->bind_param('iii', $idContatore, $idProdotto, $quantita);
                $stmt->execute();
            }
            return array('redirect' => 'prodotti_contatori');
        }
        return array('msg' => 'VENDUTO E PRODOTTO OBBLIGATORI', 'riga' => $rigaInModifica);
    }

    // Option venduti per il form regole.
    public static function opzioni($db): array
    {
        $out = array();
        $res = \mysql_query_safe($db, "SELECT id_contatore, nome FROM `contatori` ORDER BY nome");
        while ($opzione = $res->fetch_assoc()) {
            $out[] = $opzione;
        }
        return $out;
    }
}
