<?php
declare(strict_types=1);
// reserved/stat_pdf.php — export PDF della tab riservata Statistiche (#95).
// Deciso in #90 (PDF = si): FPDF leggero dagli stessi dati della pagina
// (src/Stats/StatsData.php, KPI tutto: incasso fine-giornata + per-prodotto + fasce +
// per-giorno), servito per-richiesta in download, mai scritto su disco
// (niente path web-diretti, niente file condivisi, niente race).
// Solo lettura via GET come i filtri di statistiche.php: nessuna mutazione,
// quindi nessun token (stesso precedente della pagina #93). Auth di sessione
// come da M1. Niente libchart mai: solo testo e tabelle.
// In CLI il file definisce solo le funzioni (harness di verifica, mai su master).

// FPDF ragiona in latin-1 sui font core: dal DB arriva UTF-8.
function stat_pdf_testo($s)
{
    $s = (string)$s;
    $latin = iconv('UTF-8', 'CP1252//TRANSLIT', $s);
    return ($latin === false) ? $s : $latin;
}

function stat_pdf_euro($n)
{
    // UTF-8: la conversione latin-1 avviene una sola volta in stat_pdf_riga.
    return '€ ' . number_format((float)$n, 2, ',', '.');
}

class StatPDF extends FPDF
{
    public $giornata = '';

    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $festa = \Salsiccia\Config\CassaFlags::festaLeggi();
        $nomeFesta = isset($festa['event_name']) ? (string)$festa['event_name'] : (defined('EVENT_NAME') ? (string)EVENT_NAME : '');
        $this->Cell(0, 9, stat_pdf_testo(strtoupper($nomeFesta)), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, stat_pdf_testo('STATISTICHE — GIORNATA FISCALE ' . $this->giornata), 0, 1, 'C');
        $this->Ln(2);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 10, stat_pdf_testo('Pagina ' . $this->PageNo()), 0, 0, 'C');
    }
}

// Riga tabella: celle bordate, prima colonna allineata a sinistra.
function stat_pdf_riga($pdf, $celle, $larghezze, $bold = false, $destra = array())
{
    $pdf->SetFont('Arial', $bold ? 'B' : '', 10);
    foreach ($celle as $i => $cella)
    {
        $pdf->Cell($larghezze[$i], 7, stat_pdf_testo($cella), 1, 0, in_array($i, $destra, true) ? 'R' : 'L');
    }
    $pdf->Ln();
}

function stat_pdf_titolo($pdf, $titolo)
{
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(3);
    $pdf->Cell(0, 8, stat_pdf_testo($titolo), 0, 1, 'L');
}

// $dati da StatsData::caricaDati(): solo i KPI decisi in #90, niente chart.
function stat_pdf_render($dati)
{
    $pdf = new StatPDF();
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->giornata = ($dati['oraErrore'] !== '') ? 'ERRORE: ' . $dati['oraErrore'] : ($dati['inizioGiorno'] . ' -> ' . $dati['fineGiorno']);
    $pdf->AddPage();
    $incasso = $dati['incasso'];

    stat_pdf_titolo($pdf, 'INCASSO FINE-GIORNATA');
    stat_pdf_riga($pdf, array('ORDINI', 'PEZZI', 'TOTALE'), array(60, 60, 70), true);
    stat_pdf_riga($pdf, array((string)(int)$incasso['ordini'], (string)(int)$incasso['pezzi'], stat_pdf_euro($incasso['totale'])), array(60, 60, 70));

    stat_pdf_titolo($pdf, 'PER-PRODOTTO');
    stat_pdf_riga($pdf, array('PRODOTTO', 'QTA', 'TOTALE'), array(110, 30, 50), true);
    if (count($dati['righeProdotto']) === 0)
    {
        stat_pdf_riga($pdf, array('NESSUNA RIGA', '', ''), array(110, 30, 50));
    }
    foreach ($dati['righeProdotto'] as $riga)
    {
        $nome = (string)($riga['prodotto'] ?? '');
        if (function_exists('mb_substr'))
        {
            $nome = mb_substr($nome, 0, 52, 'UTF-8');
        }
        else
        {
            $nome = substr($nome, 0, 52);
        }
        stat_pdf_riga($pdf, array(strtoupper($nome), (string)(int)($riga['quantita'] ?? 0), stat_pdf_euro($riga['totale'] ?? 0)), array(110, 30, 50));
    }

    stat_pdf_titolo($pdf, 'FASCE (ORDINE FISCALE)');
    stat_pdf_riga($pdf, array('ORA', 'ORDINI', 'PEZZI', 'TOTALE'), array(40, 40, 40, 70), true);
    for ($k = 0; $k < 24; $k++)
    {
        $ora = ($dati['oraCambio'] + $k) % 24;
        $f = $dati['fasce'][$ora];
        stat_pdf_riga($pdf, array(sprintf('%02d:00', $ora), (string)(int)$f['ordini'], (string)(int)$f['pezzi'], stat_pdf_euro($f['totale'])), array(40, 40, 40, 70));
    }

    stat_pdf_titolo($pdf, 'PER-GIORNO');
    stat_pdf_riga($pdf, array('GIORNO', 'ORDINI', 'PEZZI', 'TOTALE'), array(50, 40, 40, 60), true);
    foreach ($dati['righeGiorni'] as $riga)
    {
        stat_pdf_riga($pdf, array($riga['giorno'], (string)(int)$riga['ordini'], (string)(int)$riga['pezzi'], stat_pdf_euro($riga['totale'])), array(50, 40, 40, 60));
    }
    return $pdf;
}

// Solo l'entry web gira qui: auth + requires + fetch + download per-richiesta.
if (PHP_SAPI !== 'cli')
{
    require_once __DIR__ . '/../../bootstrap.php';
    \Salsiccia\Support\Session::start();
    if (empty($_SESSION['reserved_auth']))
    {
        header('Location: login.php?msg=2');
        exit;
    }

    require_once __DIR__ . '/../../src/Stats/StatsData.php';

    $giorno = isset($_GET['giorno']) ? $_GET['giorno'] : date('Y-m-d');
    if (!\Salsiccia\Stats\StatsData::giornoValido($giorno))
    {
        $giorno = date('Y-m-d');
    }
    $ordCorrente = isset($_GET['ord']) ? $_GET['ord'] : 'totale';
    $dirCorrente = isset($_GET['dir']) && strtoupper($_GET['dir']) === 'ASC' ? 'ASC' : 'DESC';
    $dati = \Salsiccia\Stats\StatsData::caricaDati($mysqli, $giorno, \Salsiccia\Stats\StatsData::categorieGet(), \Salsiccia\Stats\StatsData::ordinaPerProdotto($ordCorrente, $dirCorrente));
    $pdf = stat_pdf_render($dati);
    $pdf->Output('stat-' . $dati['giorno'] . '.pdf', 'I');
}
