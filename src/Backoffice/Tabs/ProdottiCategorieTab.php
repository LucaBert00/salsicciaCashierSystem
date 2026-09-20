<?php

declare(strict_types=1);

namespace Salsiccia\Backoffice\Tabs;

use Salsiccia\Backoffice\VisualizzaStore;

require_once __DIR__ . '/../VisualizzaStore.php';

// Controller del tab POSIZIONI / prodotti_categorie (T20, verbatim da visualizza.php).
// Il router verifica csrf_ok() prima di salva; elimina lo verifica da se'.
final class ProdottiCategorieTab
{
    public static function config(): array
    {
        return array('label' => 'POSIZIONI', 'titolo' => 'GESTIONE POSIZIONI',
            'from' => '`prodotti_categorie` `pc` LEFT JOIN `prodotti` `p` ON `p`.`id_prodotto` = `pc`.`id_prodotto` LEFT JOIN `categorie` `c` ON `c`.`id_categoria` = `pc`.`id_categoria`',
            'select' => '`pc`.`id_prodotto`, `pc`.`id_categoria`, `pc`.`posizione`, COALESCE(`p`.`descrizione_prod`, \'(ORFANO)\') AS `prodotto`, COALESCE(`c`.`descrizione_cat`, \'(ORFANA)\') AS `categoria`',
            'cnt' => 'COUNT(*)',
            'orders' => array('prodotto' => '`prodotto`', 'categoria' => '`categoria`', 'posizione' => '`pc`.`posizione`'),
            'default' => '`pc`.`posizione` ASC',
            'search' => array('`p`.`descrizione_prod`', '`c`.`descrizione_cat`'),
            'headers' => array(array('prodotto', 'PRODOTTO'), array('categoria', 'CATEGORIA'), array('posizione', 'POS')));
    }

    // Elimina posizione puntuale su chiave tripla prodotto+categoria+posizione.
    // Ritorna array('redirect' => tab) | array('msg' => ...) | array('none' => true).
    public static function elimina($db, string $method, array $post, array $get): array
    {
        if ($method === 'POST' && isset($post['del_p'], $post['del_c'], $post['del_pos'])) {
            if (!\csrf_ok()) {
                return array('msg' => 'TOKEN NON VALIDO');
            }
            $idProdotto = (int)$post['del_p'];
            $idCategoria = (int)$post['del_c'];
            $posizione = (int)$post['del_pos'];
            $stmt = $db->prepare("DELETE FROM `prodotti_categorie` WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?");
            $stmt->bind_param('iii', $idProdotto, $idCategoria, $posizione);
            $stmt->execute();
            return array('redirect' => 'prodotti_categorie');
        }
        if (isset($get['del_p'], $get['del_c'], $get['del_pos'])) {
            return array('msg' => 'AZIONE NON VALIDA');
        }
        return array('none' => true);
    }

    // Riga posizione per edit su chiave tripla, null se assente o incompleta.
    public static function caricaModifica($db, array $get): ?array
    {
        if (!isset($get['edit_p'], $get['edit_c'], $get['edit_pos'])) {
            return null;
        }
        $idProdotto = (int)$get['edit_p'];
        $idCategoria = (int)$get['edit_c'];
        $posizione = (int)$get['edit_pos'];
        $stmt = $db->prepare("SELECT id_prodotto, id_categoria, posizione FROM `prodotti_categorie` WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?");
        $stmt->bind_param('iii', $idProdotto, $idCategoria, $posizione);
        $stmt->execute();
        $riga = $stmt->get_result()->fetch_assoc();
        return $riga ? $riga : null;
    }

    // Salva posizione: griglia 01-24, dup sulla tripla bloccato.
    // Ritorna array('redirect' => tab) o array('msg' => ..., 'riga' => ...).
    public static function salva($db, array $post): array
    {
        $idProdotto = (int)$post['id_prodotto'];
        $idCategoria = (int)$post['id_categoria'];
        $posizione = (int)$post['posizione'];
        $vecchioIdProdotto = (int)$post['oid_p'];
        $vecchioIdCategoria = (int)$post['oid_c'];
        $vecchiaPosizione = (int)$post['oid_pos'];
        $inModifica = isset($post['is_edit']) && $post['is_edit'] === '1';
        $rigaInModifica = array('id_prodotto' => $idProdotto, 'id_categoria' => $idCategoria, 'posizione' => $posizione);
        if ($idProdotto <= 0 || $idCategoria <= 0) {
            return array('msg' => 'PRODOTTO E CATEGORIA OBBLIGATORI', 'riga' => $rigaInModifica);
        }
        if ($posizione < 1 || $posizione > 24) {
            return array('msg' => 'POSIZIONE 01-24 OBBLIGATORIA', 'riga' => $rigaInModifica);
        }
        $numDuplicati = VisualizzaStore::contaRighe($db, "SELECT COUNT(*) AS c FROM `prodotti_categorie` WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?", 'iii', array($idProdotto, $idCategoria, $posizione));
        if ($inModifica && $idProdotto == $vecchioIdProdotto && $idCategoria == $vecchioIdCategoria && $posizione == $vecchiaPosizione) {
            $numDuplicati = 0;
        }
        if ($numDuplicati > 0) {
            return array('msg' => 'POSIZIONE GIA ESISTENTE', 'riga' => $rigaInModifica);
        }
        if ($inModifica) {
            $stmt = $db->prepare("UPDATE `prodotti_categorie` SET id_prodotto = ?, id_categoria = ?, posizione = ? WHERE id_prodotto = ? AND id_categoria = ? AND posizione = ?");
            $stmt->bind_param('iiiiii', $idProdotto, $idCategoria, $posizione, $vecchioIdProdotto, $vecchioIdCategoria, $vecchiaPosizione);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("INSERT INTO `prodotti_categorie` (id_prodotto, id_categoria, posizione) VALUES (?, ?, ?)");
            $stmt->bind_param('iii', $idProdotto, $idCategoria, $posizione);
            $stmt->execute();
        }
        return array('redirect' => 'prodotti_categorie');
    }

    // Option categorie + occupancy griglia 01-24 per il form posizioni.
    // Ritorna array('categorie' => ..., 'mappa' => ...).
    public static function opzioni($db): array
    {
        $categorie = array();
        $mappa = array();
        $res = \mysql_query_safe($db, "SELECT id_categoria, descrizione_cat FROM `categorie` ORDER BY descrizione_cat");
        while ($opzione = $res->fetch_assoc()) {
            $categorie[] = $opzione;
        }
        $resOcc = \mysql_query_safe($db, "SELECT id_categoria, posizione FROM `prodotti_categorie`");
        while ($occ = $resOcc->fetch_assoc()) {
            $mappa[(int)$occ['id_categoria']][] = (int)$occ['posizione'];
        }
        return array('categorie' => $categorie, 'mappa' => $mappa);
    }
}
