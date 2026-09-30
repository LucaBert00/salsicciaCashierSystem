<?php

declare(strict_types=1);

namespace Salsiccia\Stats;

// Strato dati condiviso della tab riservata Statistiche (mappa #87, Decide #90).
// T32 follow-up #43: corpo verbatim da reserved/stat_dati.inc. Lo usano la pagina
// (statistiche.php) e l'export per-richiesta (stat_pdf.php, #95): una sola sede
// per la logica fiscale, niente duplicati. Nessun output, solo dati.
final class StatsData
{
    // Ora di cambio giornata fiscale (default 5): la giornata fiscale inizia a
    // quest'ora del giorno dato e dura 24 ore, mai a mezzanotte. Valore grezzo
    // da .env (SALSICCIA_ORA_CAMBIO via set.inc): se non e intero 0..23 le query
    // usano 5 ma pagina e PDF mostrano oraCambioErrore() al posto della
    // scritta giornata, mai fallback silenzioso.
    public static function oraCambioErrore()
    {
        $raw = defined('ORA_CAMBIO_DATA') ? trim((string)ORA_CAMBIO_DATA) : '';
        if (!ctype_digit($raw) || (int)$raw < 0 || (int)$raw > 23) {
            return "ORA GIORNATA FISCALE '" . $raw . "' NON VALIDA: CORREGGI SALSICCIA_ORA_CAMBIO NEL FILE .ENV (INTERO 0-23)";
        }
        return '';
    }

    public static function oraCambio()
    {
        if (self::oraCambioErrore() !== '') {
            return 5;
        }
        return (int)ORA_CAMBIO_DATA;
    }

    // Giorni di festa coperti dalla tabella per-giorno (default 1), tetto anti-loop.
    public static function durataFesta()
    {
        if (function_exists('festa_leggi'))
        {
            $festa = festa_leggi();
            $giorni = isset($festa['durata_festa']) ? (int)$festa['durata_festa'] : 1;
        }
        else
        {
            $giorni = defined('DURATA_FESTA') ? (int)DURATA_FESTA : 1;
        }
        if ($giorni < 1) {
            $giorni = 1;
        }
        if ($giorni > 31) {
            $giorni = 31;
        }
        return $giorni;
    }

    // Limiti della giornata fiscale del giorno dato: array(inizio, fine) in
    // formato DB. $giorno in Y-m-d gia validato, $oraCambio 0..23.
    public static function limitiGiorno($giorno, $oraCambio)
    {
        $inizio = sprintf('%s %02d:00:00', $giorno, $oraCambio);
        $fine = date('Y-m-d H:i:s', strtotime($inizio) + 86400);
        return array($inizio, $fine);
    }

    // Data Y-m-d reale, niente formati liberi: tutto il resto torna a oggi.
    public static function giornoValido($giorno)
    {
        if (!is_string($giorno) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $giorno, $m)) {
            return false;
        }
        return checkdate((int)$m[2], (int)$m[3], (int)$m[1]);
    }

    // Ordinamento per-prodotto solo da whitelist fissa, mai da input grezzo.
    public static function ordinaPerProdotto($ord, $dir)
    {
        $colonne = array(
            'prodotto' => '`prodotto`',
            'quantita' => '`quantita`',
            'totale' => '`totale`',
        );
        $col = isset($colonne[$ord]) ? $colonne[$ord] : '`totale`';
        $verso = (strtoupper((string)$dir) === 'ASC') ? 'ASC' : 'DESC';
        return $col . ' ' . $verso;
    }

    // Filtro categorie da checkbox dinamiche: solo interi positivi unici (tetto 100).
    public static function categorieGet()
    {
        $categorie = array();
        if (isset($_GET['cat'])) {
            $catRaw = is_array($_GET['cat']) ? $_GET['cat'] : array($_GET['cat']);
            foreach ($catRaw as $idCat) {
                $idCat = (int)$idCat;
                if ($idCat > 0 && !in_array($idCat, $categorie, true) && count($categorie) < 100) {
                    $categorie[] = $idCat;
                }
            }
        }
        return $categorie;
    }

    // Filtro categoria come sottoquery IN: niente duplicati da join 1:N.
    // Ritorna array(sql, tipi, valori) per il bind.
    public static function filtroCategoria($categorie)
    {
        if (count($categorie) === 0) {
            return array('', '', array());
        }
        $sql = ' AND `p`.`id_prodotto` IN (SELECT `id_prodotto` FROM `prodotti_categorie` WHERE `id_categoria` IN (' . implode(',', array_fill(0, count($categorie), '?')) . '))';
        return array($sql, str_repeat('i', count($categorie)), $categorie);
    }

    // Categorie per le checkbox: query fissa, nessun input.
    public static function opzioniCategorie($mysqli)
    {
        $opzioni = array();
        $res = $mysqli->query('SELECT `id_categoria`, `descrizione_cat` FROM `categorie` ORDER BY `descrizione_cat`');
        if ($res) {
            while ($opt = $res->fetch_assoc()) {
                $opzioni[] = $opt;
            }
            $res->free();
        }
        return $opzioni;
    }

    // Tutti i KPI in un colpo. $giorno Y-m-d gia validato, $categorie da
    // categorieGet(), $ordinaPerProdotto da ordinaPerProdotto().
    // Solo ordini chiusi (chiuso <> '0' copre 'S' e '1'). Ritorna array con
    // giorno, oraCambio, oraErrore, durataFesta, inizioGiorno, fineGiorno, categorie,
    // incasso, righeProdotto, fasce, righeGiorni.
    public static function caricaDati($mysqli, $giorno, $categorie, $ordinaPerProdotto)
    {
        $oraCambio = self::oraCambio();
        $oraErrore = self::oraCambioErrore();
        $durataFesta = self::durataFesta();
        $limiti = self::limitiGiorno($giorno, $oraCambio);
        $inizioGiorno = $limiti[0];
        $fineGiorno = $limiti[1];
        $chiusoZero = '0';
        list($filtroCat, $tipiCat, $valoriCat) = self::filtroCategoria($categorie);

        // Incasso fine-giornata.
        $stmt = $mysqli->prepare('SELECT COUNT(*) AS `ordini`, COALESCE(SUM(`totale`), 0) AS `totale`, COALESCE(SUM(`n_pezzi`), 0) AS `pezzi` FROM `ordini` WHERE `chiuso` <> ? AND `data_ora` >= ? AND `data_ora` < ?');
        $stmt->bind_param('sss', $chiusoZero, $inizioGiorno, $fineGiorno);
        $stmt->execute();
        $incasso = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Per-prodotto: quantita + totale per singolo prodotto nella giornata fiscale.
        $sqlProdotto = 'SELECT `p`.`descrizione_prod` AS `prodotto`, COALESCE(SUM(`r`.`quantita`), 0) AS `quantita`, COALESCE(SUM(`r`.`totale`), 0) AS `totale` FROM `ordini` `o` JOIN `righe_ordini` `r` ON `r`.`id_ordine` = `o`.`id_ordine` JOIN `prodotti` `p` ON `p`.`id_prodotto` = `r`.`id_prodotto` WHERE `o`.`chiuso` <> ? AND `o`.`data_ora` >= ? AND `o`.`data_ora` < ?' . $filtroCat . ' GROUP BY `p`.`id_prodotto`, `p`.`descrizione_prod` ORDER BY ' . $ordinaPerProdotto;
        $stmt = $mysqli->prepare($sqlProdotto);
        $tipiProdotto = 'sss' . $tipiCat;
        $valoriProdotto = array_merge(array($chiusoZero, $inizioGiorno, $fineGiorno), $valoriCat);
        $stmt->bind_param($tipiProdotto, ...$valoriProdotto);
        $stmt->execute();
        $righeProdotto = array();
        $resProdotto = $stmt->get_result();
        while ($riga = $resProdotto->fetch_assoc()) {
            $righeProdotto[] = $riga;
        }
        $stmt->close();

        // Fasce: aggregazione oraria dentro la giornata fiscale, una riga per ora.
        $stmt = $mysqli->prepare('SELECT HOUR(`data_ora`) AS `ora`, COUNT(*) AS `ordini`, COALESCE(SUM(`totale`), 0) AS `totale`, COALESCE(SUM(`n_pezzi`), 0) AS `pezzi` FROM `ordini` WHERE `chiuso` <> ? AND `data_ora` >= ? AND `data_ora` < ? GROUP BY HOUR(`data_ora`)');
        $stmt->bind_param('sss', $chiusoZero, $inizioGiorno, $fineGiorno);
        $stmt->execute();
        $fasce = array();
        for ($h = 0; $h < 24; $h++) {
            $fasce[$h] = array('ordini' => 0, 'totale' => 0, 'pezzi' => 0);
        }
        $resFasce = $stmt->get_result();
        while ($riga = $resFasce->fetch_assoc()) {
            $fasce[(int)$riga['ora']] = array('ordini' => $riga['ordini'], 'totale' => $riga['totale'], 'pezzi' => $riga['pezzi']);
        }
        $stmt->close();

        // Per-giorno: UN solo statement sull'intero intervallo di festa, GROUP BY
        // giornata fiscale (DATE(DATE_SUB(... INTERVAL oraCambio HOUR))). Le
        // finestre fiscali partizionano l'intervallo senza buchi ne'
        // sovrapposizioni, quindi i totali sono quelli del loop di prima.
        $baseGiorno = strtotime($giorno . ' 12:00:00');
        $primaRiga = date('Y-m-d', $baseGiorno - ($durataFesta - 1) * 86400);
        $limitiPrima = self::limitiGiorno($primaRiga, $oraCambio);
        $stmtGiorno = $mysqli->prepare('SELECT DATE(DATE_SUB(`data_ora`, INTERVAL ' . (int)$oraCambio . ' HOUR)) AS `giorno`, COUNT(*) AS `ordini`, COALESCE(SUM(`totale`), 0) AS `totale`, COALESCE(SUM(`n_pezzi`), 0) AS `pezzi` FROM `ordini` WHERE `chiuso` <> ? AND `data_ora` >= ? AND `data_ora` < ? GROUP BY `giorno`');
        $stmtGiorno->bind_param('sss', $chiusoZero, $limitiPrima[0], $fineGiorno);
        $stmtGiorno->execute();
        $mappaGiorni = array();
        $resGiorno = $stmtGiorno->get_result();
        while ($rigaGiorno = $resGiorno->fetch_assoc()) {
            $mappaGiorni[$rigaGiorno['giorno']] = $rigaGiorno;
        }
        $stmtGiorno->close();
        $righeGiorni = array();
        for ($i = $durataFesta - 1; $i >= 0; $i--) {
            $giornoRiga = date('Y-m-d', $baseGiorno - $i * 86400);
            if (isset($mappaGiorni[$giornoRiga])) {
                $m = $mappaGiorni[$giornoRiga];
                $righeGiorni[] = array('giorno' => $giornoRiga, 'ordini' => $m['ordini'], 'pezzi' => $m['pezzi'], 'totale' => $m['totale']);
            } else {
                $righeGiorni[] = array('giorno' => $giornoRiga, 'ordini' => 0, 'pezzi' => 0, 'totale' => 0);
            }
        }

        return array(
            'giorno' => $giorno,
            'oraCambio' => $oraCambio,
            'oraErrore' => $oraErrore,
            'durataFesta' => $durataFesta,
            'inizioGiorno' => $inizioGiorno,
            'fineGiorno' => $fineGiorno,
            'categorie' => $categorie,
            'incasso' => $incasso,
            'righeProdotto' => $righeProdotto,
            'fasce' => $fasce,
            'righeGiorni' => $righeGiorni,
        );
    }
}
