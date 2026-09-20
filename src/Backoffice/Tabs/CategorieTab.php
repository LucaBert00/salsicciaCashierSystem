<?php

declare(strict_types=1);

namespace Salsiccia\Backoffice\Tabs;

use Salsiccia\Backoffice\VisualizzaStore;

require_once __DIR__ . '/../VisualizzaStore.php';

// Controller del tab CATEGORIE (T20, verbatim da public/reserved/visualizza.php).
// Il router verifica csrf_ok() prima di salva; elimina lo verifica da se'.
final class CategorieTab
{
    public static function config(): array
    {
        return array('label' => 'CATEGORIE', 'titolo' => 'GESTIONE CATEGORIE',
            'from' => '`categorie`',
            'select' => '`id_categoria`, `descrizione_cat`, `testo_bottone`, `colore`',
            'cnt' => 'COUNT(*)',
            'orders' => array('descrizione_cat' => '`descrizione_cat`', 'testo_bottone' => '`testo_bottone`', 'colore' => '`colore`'),
            'default' => '`descrizione_cat` ASC',
            'search' => array('`descrizione_cat`', '`testo_bottone`'),
            'headers' => array(array('descrizione_cat', 'CATEGORIA'), array('testo_bottone', 'BOTTONE'), array('colore', 'COLORE')));
    }

    // Elimina categoria solo se nessuna posizione la usa; GET resta lettura.
    // Ritorna array('redirect' => tab) | array('msg' => ...) | array('none' => true).
    public static function elimina($db, string $method, array $post, array $get): array
    {
        if ($method === 'POST' && isset($post['del'])) {
            if (!\csrf_ok()) {
                return array('msg' => 'TOKEN NON VALIDO');
            }
            $idCategoria = (int)$post['del'];
            if (VisualizzaStore::contaRighe($db, "SELECT COUNT(*) AS c FROM `prodotti_categorie` WHERE id_categoria = ?", 'i', array($idCategoria)) > 0) {
                return array('msg' => 'CATEGORIA REFERENZIATA — ELIMINA BLOCCATA');
            }
            $stmt = $db->prepare("DELETE FROM `categorie` WHERE id_categoria = ?");
            $stmt->bind_param('i', $idCategoria);
            $stmt->execute();
            return array('redirect' => 'categorie');
        }
        if (isset($get['del'])) {
            return array('msg' => 'AZIONE NON VALIDA');
        }
        return array('none' => true);
    }

    // Riga categoria per edit, null se assente o senza chiave.
    public static function caricaModifica($db, array $get): ?array
    {
        if (!isset($get['edit'])) {
            return null;
        }
        $idCategoria = (int)$get['edit'];
        $stmt = $db->prepare("SELECT id_categoria, descrizione_cat, testo_bottone, colore FROM `categorie` WHERE id_categoria = ?");
        $stmt->bind_param('i', $idCategoria);
        $stmt->execute();
        $riga = $stmt->get_result()->fetch_assoc();
        return $riga ? $riga : null;
    }

    // Salva categoria: descrizione, bottone e colore obbligatori.
    // Ritorna array('redirect' => tab) o array('msg' => ..., 'riga' => ...).
    public static function salva($db, array $post): array
    {
        $idCategoria = (int)$post['id'];
        $descrizione = substr(trim($post['descrizione_cat']), 0, 30);
        $testoBottone = substr(trim($post['testo_bottone']), 0, 20);
        $coloreInput = substr(trim($post['colore']), 0, 20);
        if ($descrizione === '' || $testoBottone === '' || $coloreInput === '') {
            return array('msg' => 'DESCRIZIONE, BOTTONE E COLORE OBBLIGATORI',
                'riga' => array('id_categoria' => $idCategoria, 'descrizione_cat' => $descrizione, 'testo_bottone' => $testoBottone, 'colore' => $coloreInput));
        }
        $colore = VisualizzaStore::normalizzaColore($coloreInput);
        if ($colore === false) {
            return array('msg' => 'COLORE NON VALIDO - USA NOME O NUMERO',
                'riga' => array('id_categoria' => $idCategoria, 'descrizione_cat' => $descrizione, 'testo_bottone' => $testoBottone, 'colore' => $coloreInput));
        }
        if ($idCategoria > 0) {
            $stmt = $db->prepare("UPDATE `categorie` SET descrizione_cat = ?, testo_bottone = ?, colore = ? WHERE id_categoria = ?");
            $stmt->bind_param('sssi', $descrizione, $testoBottone, $colore, $idCategoria);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("INSERT INTO `categorie` (descrizione_cat, testo_bottone, colore) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $descrizione, $testoBottone, $colore);
            $stmt->execute();
        }
        return array('redirect' => 'categorie');
    }
}
