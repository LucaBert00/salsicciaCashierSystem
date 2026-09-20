<?php

declare(strict_types=1);

namespace Salsiccia\Backoffice\Tabs;

use Salsiccia\Backoffice\VisualizzaStore;

require_once __DIR__ . '/../VisualizzaStore.php';

// Controller del tab PRODOTTI VENDUTI / contatori (T20, verbatim da visualizza.php).
// Il router verifica csrf_ok() prima di salva; elimina lo verifica da se'.
final class ContatoriTab
{
    public static function config(): array
    {
        return array('label' => 'PRODOTTI VENDUTI', 'titolo' => 'GESTIONE PRODOTTI VENDUTI',
            'from' => '`contatori`',
            'select' => '`id_contatore`, `nome`, `limite_qta`, `controllo_periodo`, `data_da`, `data_a`, `attivo`, `attivo_app`',
            'cnt' => 'COUNT(*)',
            'orders' => array('nome' => '`nome`', 'limite_qta' => '`limite_qta`', 'attivo' => '`attivo`'),
            'default' => '`nome` ASC',
            'search' => array('`nome`'),
            'headers' => array(array('nome', 'NOME'), array('limite_qta', 'LIMITE'), array('attivo', 'ATTIVO')));
    }

    // Elimina venduto a cascata con le sue regole (MyISAM = zero FK).
    // Ritorna array('redirect' => tab) | array('msg' => ...) | array('none' => true).
    public static function elimina($db, string $method, array $post, array $get): array
    {
        if ($method === 'POST' && isset($post['del'])) {
            if (!\csrf_ok()) {
                return array('msg' => 'TOKEN NON VALIDO');
            }
            $idContatore = (int)$post['del'];
            $stmt = $db->prepare("DELETE FROM `prodotti_contatori` WHERE id_contatore = ?");
            $stmt->bind_param('i', $idContatore);
            $stmt->execute();
            $stmt->close();
            $stmt = $db->prepare("DELETE FROM `contatori` WHERE id_contatore = ?");
            $stmt->bind_param('i', $idContatore);
            $stmt->execute();
            return array('redirect' => 'contatori');
        }
        if (isset($get['del'])) {
            return array('msg' => 'AZIONE NON VALIDA');
        }
        return array('none' => true);
    }

    // Riga venduto per edit, null se assente o senza chiave.
    public static function caricaModifica($db, array $get): ?array
    {
        if (!isset($get['edit'])) {
            return null;
        }
        $idContatore = (int)$get['edit'];
        $stmt = $db->prepare("SELECT id_contatore, nome, limite_qta, controllo_periodo, data_da, data_a, attivo, attivo_app FROM `contatori` WHERE id_contatore = ?");
        $stmt->bind_param('i', $idContatore);
        $stmt->execute();
        $riga = $stmt->get_result()->fetch_assoc();
        return $riga ? $riga : null;
    }

    // Salva venduto: nome e date obbligatori, flag T/F e date in formato DB.
    // Ritorna array('redirect' => tab) o array('msg' => ..., 'riga' => ...).
    public static function salva($db, array $post): array
    {
        $idContatore = (int)$post['id'];
        $nome = substr(trim($post['nome']), 0, 50);
        $limiteQta = max(0, (int)$post['limite_qta']);
        $flagPeriodo = (isset($post['controllo_periodo']) && $post['controllo_periodo'] == 'T') ? 'T' : 'F';
        $tsDa = strtotime($post['data_da']);
        $tsA = strtotime($post['data_a']);
        $flagAttivo = (isset($post['attivo']) && $post['attivo'] == 'T') ? 'T' : 'F';
        $flagApp = (isset($post['attivo_app']) && $post['attivo_app'] == 'T') ? 'T' : 'F';
        if ($nome !== '' && $tsDa !== false && $tsA !== false) {
            $dataDaDb = date('Y-m-d H:i:s', $tsDa);
            $dataADb = date('Y-m-d H:i:s', $tsA);
            if ($idContatore > 0) {
                $stmt = $db->prepare("UPDATE `contatori` SET nome = ?, limite_qta = ?, controllo_periodo = ?, data_da = ?, data_a = ?, attivo = ?, attivo_app = ? WHERE id_contatore = ?");
                $stmt->bind_param('sisssssi', $nome, $limiteQta, $flagPeriodo, $dataDaDb, $dataADb, $flagAttivo, $flagApp, $idContatore);
                $stmt->execute();
            } else {
                $stmt = $db->prepare("INSERT INTO `contatori` (nome, limite_qta, controllo_periodo, data_da, data_a, attivo, attivo_app) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sisssss', $nome, $limiteQta, $flagPeriodo, $dataDaDb, $dataADb, $flagAttivo, $flagApp);
                $stmt->execute();
            }
            return array('redirect' => 'contatori');
        }
        return array('msg' => 'NOME E DATE OBBLIGATORI',
            'riga' => array('id_contatore' => $idContatore, 'nome' => $nome, 'limite_qta' => $limiteQta, 'controllo_periodo' => $flagPeriodo, 'data_da' => $post['data_da'], 'data_a' => $post['data_a'], 'attivo' => $flagAttivo, 'attivo_app' => $flagApp));
    }
}
