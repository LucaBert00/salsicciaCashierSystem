<?php

declare(strict_types=1);

namespace Salsiccia\Backoffice;

// Casa condivisa della pipeline lista 5 tab (T20, da public/reserved/visualizza.php).
// G3 preservata verbatim: ORDER BY solo da whitelist, LIKE solo bound, LIMIT solo (int).
// DB iniettato per parametro, mai globale (come CatalogRepo/OrderService).
// I 5 tab (src/Backoffice/Tabs/*Tab.php) forniscono config + delete/save/edit;
// questo Store fornisce solo cio' che e' davvero condiviso.
final class VisualizzaStore
{
    // Whitelist ORDER BY: colonna nota -> frammento SQL + direzione gia' validata,
    // altrimenti default del tab. Mai input grezzo (G3).
    public static function resolveOrderBy(array $orders, string $default, string $colonnaOrd, string $direzioneOrd): string
    {
        if ($colonnaOrd !== '' && isset($orders[$colonnaOrd])) {
            return $orders[$colonnaOrd] . ' ' . $direzioneOrd;
        }
        return $default;
    }

    // WHERE di ricerca: un LIKE ? bound per colonna, mai interpolazione (G3).
    // Ritorna array($where, $tipi, $vals).
    public static function buildSearch(array $colonneRicerca, string $ricerca): array
    {
        if ($ricerca === '') {
            return array('', '', array());
        }
        $w = array();
        foreach ($colonneRicerca as $colRicerca) {
            $w[] = $colRicerca . ' LIKE ?';
        }
        $where = ' WHERE (' . implode(' OR ', $w) . ')';
        $tipi = '';
        $vals = array();
        foreach ($colonneRicerca as $colRicerca) {
            $tipi .= 's';
            $vals[] = '%' . $ricerca . '%';
        }
        return array($where, $tipi, $vals);
    }

    // COUNT + SELECT paginata con la stessa WHERE; pagina clampata, offset/rpp (int) (G3).
    // Ritorna array($totRighe, $totPagine, $pagina, $righe).
    public static function fetchPagina($db, array $cfg, string $orderBySql, string $where, string $tipi, array $vals, int $pagina, int $rpp): array
    {
        $stmt = $db->prepare("SELECT " . $cfg['cnt'] . " AS c FROM " . $cfg['from'] . $where);
        if ($tipi !== '') {
            $stmt->bind_param($tipi, ...$vals);
        }
        $stmt->execute();
        $totRighe = (int)$stmt->get_result()->fetch_assoc()['c'];
        $totPagine = max(1, (int)ceil($totRighe / $rpp));
        if ($pagina > $totPagine) {
            $pagina = $totPagine;
        }
        $offset = ($pagina - 1) * $rpp;
        $stmt = $db->prepare("SELECT " . $cfg['select'] . " FROM " . $cfg['from'] . $where . " ORDER BY " . $orderBySql . " LIMIT " . (int)$offset . ", " . (int)$rpp);
        if ($tipi !== '') {
            $stmt->bind_param($tipi, ...$vals);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $righe = array();
        while ($riga = $res->fetch_assoc()) {
            $righe[] = $riga;
        }
        return array($totRighe, $totPagine, $pagina, $righe);
    }

    // Barcode facoltativo (#47, da e6559f4): la colonna puo mancare nei DB
    // storici. Sonda SHOW COLUMNS con whitelist sugli identificatori e
    // fail-open true su qualsiasi errore: una sonda rotta non deve mai
    // murare il backoffice sul laptop di fiera.
    public static function haColonna($db, string $tabella, string $colonna): bool
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $tabella) || !preg_match('/^[A-Za-z0-9_]+$/', $colonna)) {
            return true;
        }
        try {
            $res = $db->query("SHOW COLUMNS FROM `" . $tabella . "` LIKE '" . $colonna . "'");
            if (!$res) {
                return true;
            }
            return $res->num_rows > 0;
        } catch (\Throwable $e) {
            return true;
        }
    }

    // Conta righe con prepared per controlli referenziali e duplicati (verbatim).
    public static function contaRighe($db, string $sql, string $tipiBind, array $valoriBind): int
    {
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException($db->error !== '' ? $db->error : 'prepare fallita');
        }
        if ($tipiBind !== '') {
            $stmt->bind_param($tipiBind, ...$valoriBind);
        }
        $stmt->execute();
        $conteggio = (int)$stmt->get_result()->fetch_assoc()['c'];
        $stmt->close();
        return $conteggio;
    }

    // Link a visualizza.php con parametri url-encoded (verbatim).
    public static function urlLista(array $params): string
    {
        return 'visualizza.php?' . http_build_query($params);
    }

    // Dizionario colori 01..14: unico punto di verita' per normalizzazione +
    // rendering. Salvataggio = numero zero-padded; lista = NOME(XX) (verbatim).
    public static function mappaColori(): array
    {
        static $mappa = array(
            'ROSSO' => '01', 'ARANCIONE' => '02', 'OCRA' => '03', 'GIALLO' => '04',
            'VERDE' => '05', 'VERDE SCURO' => '06', 'AZZURRO' => '07', 'BLU' => '08',
            'VIOLA' => '09', 'LILLA' => '10', 'VIOLA CHIARO' => '11', 'ROSA SCURO' => '12',
            'ROSA' => '13', 'ROSA CHIARO' => '14',
        );
        return $mappa;
    }

    // Normalizza COLORE a numero zero-padded, false se rifiutato (verbatim).
    public static function normalizzaColore(string $raw)
    {
        $colore = strtoupper(trim($raw));
        if ($colore === '' || strpos($colore, '(') !== false || strpos($colore, ')') !== false) {
            return false;
        }
        $mappa = self::mappaColori();
        if (isset($mappa[$colore])) {
            return $mappa[$colore];
        }
        $compatto = str_replace(' ', '', $colore);
        foreach ($mappa as $nome => $codice) {
            if (str_replace(' ', '', $nome) === $compatto) {
                return $codice;
            }
        }
        if (ctype_digit($colore)) {
            $num = (int)$colore;
            if ($num >= 1 && $num <= 14) {
                return sprintf('%02d', $num);
            }
        }
        return false;
    }

    // Etichetta lista COLORE: NOME(XX), raw se ignoto (verbatim).
    public static function etichettaColore($codice): string
    {
        $codice = strtoupper(trim((string)$codice));
        if (ctype_digit($codice)) {
            $codice = sprintf('%02d', (int)$codice);
        }
        $nome = array_search($codice, self::mappaColori(), true);
        if ($nome !== false) {
            return $nome . '(' . $codice . ')';
        }
        return $codice;
    }

    // Flag T/F del DB in SI/NO per la lista (verbatim).
    public static function etichettaSiNo($flag): string
    {
        if ($flag === 'T') {
            return 'SI';
        }
        return 'NO';
    }

    // Modalita' fiera via store JSON (T17), mai parse di set.inc (verbatim).
    public static function fieraAttiva(): bool
    {
        return \Salsiccia\Config\CassaFlags::cassaLeggiFiera();
    }

    // Errore DB grezzo in messaggio leggibile da cassa (verbatim).
    public static function erroreSchemaMessaggio(string $dettaglio): string
    {
        if (preg_match("/Unknown column '([^']+)'/i", $dettaglio, $m)) {
            return "COLONNA '" . $m[1] . "' MANCANTE — CONTATTA ASSISTENZA";
        }
        if (preg_match("/Table '([^']+)' doesn't exist/i", $dettaglio, $m)) {
            $tabella = $m[1];
            $pos = strrpos($tabella, '.');
            if ($pos !== false) {
                $tabella = substr($tabella, $pos + 1);
            }
            return "TABELLA '" . $tabella . "' MANCANTE — CONTATTA ASSISTENZA";
        }
        return 'ERRORE DB — CONTATTA ASSISTENZA';
    }

    // Option prodotti per i due form ponte (query statica, T09-safe).
    public static function opzioniProdotti($db): array
    {
        $out = array();
        $res = \mysql_query_safe($db, "SELECT id_prodotto, descrizione_prod FROM `prodotti` ORDER BY descrizione_prod");
        while ($opzione = $res->fetch_assoc()) {
            $out[] = $opzione;
        }
        return $out;
    }
}
