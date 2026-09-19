<?php
// reserved/statistiche.php — tab riservata Statistiche: tabelle primarie per la fiera.
// Deciso in #90: KPI tutto (incasso fine-giornata + per-prodotto + fasce + per-giorno),
// tabella primaria e SVG secondario (SVG nel ticket dedicato), niente libchart,
// niente file immagine condiviso, output per-richiesta. Solo lettura: filtri via GET,
// nessuna mutazione quindi nessun token (come ricerca/ordinamento in visualizza.php).
// Filtri + query in stat_dati.inc (#95, condiviso con l'export PDF per-richiesta).

session_start();
if (empty($_SESSION['reserved_auth']))
{
    header('Location: login.php?msg=2');
    exit;
}

require_once __DIR__ . '/../dbConnect.php';
require_once __DIR__ . '/../set.inc';

if (!defined('DEBUG'))
{
    define('DEBUG', 0);
}
require_once __DIR__ . '/../funzioni.inc';
require_once __DIR__ . '/stat_dati.inc';

// Link a statistiche.php con parametri dati, gia url-encoded.
function urlStat($params)
{
    return 'statistiche.php?' . http_build_query($params);
}

// Palette fissa dei chart (stessa del prototype #94, variante A): ciclo su N prodotti.
function stat_colore($i)
{
    static $colori = array('#2b3d4e', '#5b7fa6', '#8fa9bf', '#c2cfdb', '#e0e6ec');
    return $colori[$i % count($colori)];
}

// Donut per-prodotto dai dati live di #93: quote sul totale, SVG inline
// per-richiesta, mai file condiviso. Vuoto se niente da disegnare.
function stat_svg_donut($righeProdotto, $totale)
{
    $totale = (float)$totale;
    if ($totale <= 0 || count($righeProdotto) === 0)
    {
        return '';
    }
    $conf = array();
    $somma = 0.0;
    foreach ($righeProdotto as $riga)
    {
        $t = max(0.0, (float)($riga['totale'] ?? 0));
        $somma += $t;
        $conf[] = $t;
    }
    if ($somma <= 0)
    {
        return '';
    }
    $c = 2 * M_PI * 80;
    $cumul = 0.0;
    $cerchi = '';
    foreach ($conf as $i => $t)
    {
        $quota = $t / $somma;
        if ($quota <= 0)
        {
            continue;
        }
        $lungo = $quota * $c;
        $cerchi .= sprintf(
            '<circle cx="110" cy="110" r="80" fill="none" stroke="%s" stroke-width="44" stroke-dasharray="%s %s" stroke-dashoffset="%s" transform="rotate(-90 110 110)"/>',
            stat_colore($i),
            number_format($lungo, 2, '.', ''),
            number_format($c - $lungo, 2, '.', ''),
            number_format(-$cumul, 2, '.', '')
        );
        $cumul += $lungo;
    }
    $voci = array();
    foreach ($righeProdotto as $i => $riga)
    {
        $t = max(0.0, (float)($riga['totale'] ?? 0));
        if ($t <= 0)
        {
            continue;
        }
        $voci[] = strtoupper($riga['prodotto'] ?? '') . ' ' . number_format($t / $somma * 100, 1, ',', '.') . '%';
    }
    return '<svg class="stat-chart" style="max-width:200px;" viewBox="0 0 220 220" role="img" aria-labelledby="statDonutT statDonutD">'
        . '<title id="statDonutT">Donut per-prodotto</title>'
        . '<desc id="statDonutD">' . htmlspecialchars(implode(', ', $voci)) . '. I numeri in tabella comandano.</desc>'
        . '<circle cx="110" cy="110" r="80" fill="none" stroke="#e5e6eb" stroke-width="44"/>'
        . $cerchi
        . '<text x="110" y="105" text-anchor="middle" font-size="22" font-weight="800" fill="#2b3d4e">&euro; ' . htmlspecialchars(number_format($totale, 2, ',', '.')) . '</text>'
        . '<text x="110" y="126" text-anchor="middle" font-size="12" font-weight="700" fill="#5b6b7b">INCASSO FINE-GIORNATA</text>'
        . '</svg>';
}

// Istogramma affluenza: barre verticali raggruppate ordini+pezzi per fascia
// attiva, in ordine fiscale da $oraCambio. Valori scritti sopra le barre.
function stat_svg_affluenza($fasce, $oraCambio)
{
    $attive = array();
    for ($k = 0; $k < 24; $k++)
    {
        $ora = ($oraCambio + $k) % 24;
        $f = $fasce[$ora] ?? array('ordini' => 0, 'pezzi' => 0);
        $o = (int)($f['ordini'] ?? 0);
        $p = (int)($f['pezzi'] ?? 0);
        if ($o > 0 || $p > 0)
        {
            $attive[] = array('ora' => $ora, 'ordini' => $o, 'pezzi' => $p);
        }
    }
    if (count($attive) === 0)
    {
        return '';
    }
    $max = 1;
    foreach ($attive as $a)
    {
        $max = max($max, $a['ordini'], $a['pezzi']);
    }
    $hGraf = 150;
    $padSopra = 26;
    $padSotto = 26;
    $largBarra = 16;
    $spazioGruppo = 6;
    $spazioTra = 18;
    $padX = 12;
    $n = count($attive);
    $larghezza = $padX * 2 + $n * ($largBarra * 2 + $spazioGruppo) + ($n - 1) * $spazioTra;
    // Tetto altezza #114 (Decide D): viewBox mai piu stretto di 8 bande
    // (454u = 24 + 8*38 + 7*18), cosi con width:100%+height:auto l'altezza
    // render resta ~355px per n<=8; barre e hGraf invariati, mai deformate.
    $larghezza = max($larghezza, 454);
    $altezza = $padSopra + $hGraf + $padSotto;
    $base = $padSopra + $hGraf;
    $svg = '';
    $x = (float)$padX;
    foreach ($attive as $a)
    {
        $hO = $hGraf * $a['ordini'] / $max;
        $hP = $hGraf * $a['pezzi'] / $max;
        if ($hO > 0)
        {
            $svg .= sprintf(
                '<rect x="%s" y="%s" width="%d" height="%s" fill="#2b3d4e"/>',
                number_format($x, 1, '.', ''), number_format($base - $hO, 1, '.', ''), $largBarra, number_format($hO, 1, '.', '')
            );
            $svg .= sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-size="11" font-weight="800" fill="#2b3d4e">%d</text>',
                number_format($x + $largBarra / 2, 1, '.', ''), number_format($base - $hO - 4, 1, '.', ''), $a['ordini']
            );
        }
        if ($hP > 0)
        {
            $svg .= sprintf(
                '<rect x="%s" y="%s" width="%d" height="%s" fill="#5b7fa6"/>',
                number_format($x + $largBarra + $spazioGruppo, 1, '.', ''), number_format($base - $hP, 1, '.', ''), $largBarra, number_format($hP, 1, '.', '')
            );
            $svg .= sprintf(
                '<text x="%s" y="%s" text-anchor="middle" font-size="11" font-weight="800" fill="#2b3d4e">%d</text>',
                number_format($x + $largBarra + $spazioGruppo + $largBarra / 2, 1, '.', ''), number_format($base - $hP - 4, 1, '.', ''), $a['pezzi']
            );
        }
        $svg .= sprintf(
            '<text x="%s" y="%d" text-anchor="middle" font-size="11" font-weight="800" fill="#2b3d4e">%02d:00</text>',
            number_format($x + $largBarra + $spazioGruppo / 2, 1, '.', ''), $base + 18, $a['ora']
        );
        $x += $largBarra * 2 + $spazioGruppo + $spazioTra;
    }
    $desc = array();
    foreach ($attive as $a)
    {
        $desc[] = sprintf('%02d:00 %d ordini %d pezzi', $a['ora'], $a['ordini'], $a['pezzi']);
    }
    return '<svg class="stat-chart" viewBox="0 0 ' . $larghezza . ' ' . $altezza . '" role="img" aria-labelledby="statAffT statAffD">'
        . '<title id="statAffT">Affluenza per fascia, ordini e pezzi</title>'
        . '<desc id="statAffD">' . htmlspecialchars(implode(', ', $desc)) . '. I numeri in tabella comandano.</desc>'
        . $svg
        . '<line x1="' . $padX . '" y1="' . $base . '" x2="' . ($larghezza - $padX) . '" y2="' . $base . '" stroke="#dcdfe6"/>'
        . '</svg>';
}

// Filtri GET in sola lettura + tutti i KPI via strato condiviso (#95).
$giorno = isset($_GET['giorno']) ? $_GET['giorno'] : date('Y-m-d');
if (!stat_giorno_valido($giorno))
{
    $giorno = date('Y-m-d');
}

// Ordinamento per-prodotto validato, default totale giu (i piu venduti prima).
$ordCorrente = isset($_GET['ord']) ? $_GET['ord'] : 'totale';
$dirCorrente = isset($_GET['dir']) && strtoupper($_GET['dir']) === 'ASC' ? 'ASC' : 'DESC';
$ordinaPerProdotto = stat_ordina_per_prodotto($ordCorrente, $dirCorrente);

// Viste ibride (#109, Decide ibrido): una sezione alla volta via GET,
// whitelist fissa, default INCASSO. Paginazione 5/pagina stile gestioni
// (visualizza.php:34 + :814-832): COUNT su array, LIMIT via array_slice,
// clamp come :824-827. Donut/legenda/istogramma usano sempre i dati interi.
$visteStat = array(
    'incasso' => 'INCASSO',
    'prodotto' => 'PER-PRODOTTO',
    'fasce' => 'FASCE',
    'giorni' => 'PER-GIORNO',
);
$vistaCorrente = (isset($_GET['vista']) && isset($visteStat[$_GET['vista']])) ? $_GET['vista'] : 'incasso';
// #120: prodotto con donut a 3/pagina per stare one-shot con grafico, resto a 5.
$righePerPaginaStat = ($vistaCorrente === 'prodotto') ? 3 : 5;
$paginaStat = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
if ($paginaStat < 1)
{
    $paginaStat = 1;
}

$categorie = stat_categorie_get();
$dati = stat_carica_dati($mysqli, $giorno, $categorie, $ordinaPerProdotto);
$oraCambio = $dati['oraCambio'];
$oraErrore = $dati['oraErrore'];
$durataFesta = $dati['durataFesta'];
$inizioGiorno = $dati['inizioGiorno'];
$fineGiorno = $dati['fineGiorno'];
$incasso = $dati['incasso'];
$righeProdotto = $dati['righeProdotto'];
$fasce = $dati['fasce'];
$righeGiorni = $dati['righeGiorni'];

// Categorie per le checkbox: query fissa, nessun input.
$opzioniCategorie = stat_opzioni_categorie($mysqli);

// Parametri filtri da conservare nei link di ordinamento.
$paramsFiltri = array('giorno' => $giorno);
if (count($categorie) > 0)
{
    $paramsFiltri['cat'] = $categorie;
}

// Link vista/pagina che conservano filtri + ordinamento + vista.
function urlStatVista($paramsFiltri, $vista, $ord, $dir, $pag)
{
    return urlStat($paramsFiltri + array('vista' => $vista, 'ord' => $ord, 'dir' => $dir, 'pag' => $pag));
}

// Fasce in ordine fiscale per la tabella paginata (istogramma invariato).
$righeFasce = array();
for ($kFascia = 0; $kFascia < 24; $kFascia++)
{
    $oraFascia = ($oraCambio + $kFascia) % 24;
    $fFascia = $fasce[$oraFascia];
    $righeFasce[] = array('ora' => $oraFascia, 'ordini' => $fFascia['ordini'], 'pezzi' => $fFascia['pezzi'], 'totale' => $fFascia['totale']);
}

// Paginazione della sola vista corrente (le altre ripartono da 1).
if ($vistaCorrente === 'prodotto')
{
    $righeVistaStat = $righeProdotto;
}
elseif ($vistaCorrente === 'fasce')
{
    $righeVistaStat = $righeFasce;
}
elseif ($vistaCorrente === 'giorni')
{
    $righeVistaStat = $righeGiorni;
}
else
{
    $righeVistaStat = array();
}
$totPagineStat = max(1, (int)ceil(count($righeVistaStat) / $righePerPaginaStat));
if ($paginaStat > $totPagineStat)
{
    $paginaStat = $totPagineStat;
}
$offsetStat = ($paginaStat - 1) * $righePerPaginaStat;
$righePaginaStat = array_slice($righeVistaStat, $offsetStat, $righePerPaginaStat);

// Pager compatto stile gestioni (visualizza.php:1226-1242).
function stat_pager($paramsFiltri, $vista, $ord, $dir, $pagina, $totPagine)
{
    $html = '<div class="admin-btn-row">';
    if ($pagina > 1)
    {
        $html .= '<a href="' . htmlspecialchars(urlStatVista($paramsFiltri, $vista, $ord, $dir, $pagina - 1)) . '" class="opzione-btn" style="padding:12px 20px;">&lt;</a>';
    }
    foreach (array_unique(array(1, $pagina - 1, $pagina, $pagina + 1, $totPagine)) as $numPagina)
    {
        if ($numPagina < 1 || $numPagina > $totPagine)
        {
            continue;
        }
        if ($numPagina == $pagina)
        {
            $html .= '<span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;">' . $numPagina . '</span>';
        }
        else
        {
            $html .= '<a href="' . htmlspecialchars(urlStatVista($paramsFiltri, $vista, $ord, $dir, $numPagina)) . '" class="opzione-btn" style="padding:12px 20px;">' . $numPagina . '</a>';
        }
    }
    if ($pagina < $totPagine)
    {
        $html .= '<a href="' . htmlspecialchars(urlStatVista($paramsFiltri, $vista, $ord, $dir, $pagina + 1)) . '" class="opzione-btn" style="padding:12px 20px;">&gt;</a>';
    }
    return $html . '</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STATISTICHE</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        svg.stat-chart{width:100%;height:auto;background:#fff;border:1px solid #dcdfe6;border-radius:8px;margin-top:8px;}
        ul.stat-legend{list-style:none;margin:8px 0 0;padding:0;font-size:15px;font-weight:700;text-transform:uppercase;color:#2b3d4e;}
        ul.stat-legend li{display:flex;gap:8px;align-items:center;padding:2px 0;}
        ul.stat-legend .swatch{display:inline-block;width:12px;height:12px;border-radius:2px;flex:none;}
        /* #120: donut ingrandito + legenda a dx. Flex con wrap naturale (mai breakpoint/
           container query per legge di scala): a 1280 sta affiancato, su stretto va sotto
           senza overflow. Altezze limitate: chart max 200px per stare one-shot con
           tabella 5 righe + pager a 1280x1024, legenda scroll interno. */
        div.stat-donut{display:flex;flex-wrap:wrap;gap:12px;align-items:flex-start;margin-top:8px;}
        div.stat-donut svg.stat-chart{flex:0 1 200px;max-width:200px;min-width:0;margin-top:0;}
        div.stat-donut ul.stat-legend{flex:1 1 240px;min-width:0;margin:0;max-height:200px;overflow-y:auto;}
        p.stat-note{font-size:13px;font-weight:700;text-transform:uppercase;color:#5b6b7b;margin:8px 0 0;}
        div.stat-scroll{overflow-x:auto;}
        div.stat-scroll svg.stat-chart{min-width:480px;}
    </style>
</head>
<body>
    <main>
        <header><h1>STATISTICHE</h1><h3 class="admin-group-title" style="margin-left:auto;margin-bottom:0;"><?php if ($oraErrore !== ''): ?>ERRORE: <?php echo htmlspecialchars($oraErrore); ?><?php else: ?>GIORNATA FISCALE <?php echo htmlspecialchars($inizioGiorno); ?> &#8594; <?php echo htmlspecialchars($fineGiorno); ?><?php endif; ?></h3></header>
        <!-- Fascia alta fissa (#113): filtri invariati; flex:none = alta quanto il contenuto, mai scroll -->
        <section class="admin-panel" style="flex:none;">
            <!-- Filtri GET in sola lettura: giorno nativo + categorie dinamiche -->
            <form action="statistiche.php" method="get">
                <input type="date" name="giorno" class="codice-text-field" required value="<?php echo htmlspecialchars($giorno); ?>" style="margin-bottom:0;">
                <!-- Filtri categoria come vero filtro (#108, variante B): fieldset a sinistra, azioni a destra sulla stessa riga -->
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:8px;">
                    <fieldset style="border:2px solid #2b3d4e;border-radius:8px;padding:6px 10px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin:0;">
                        <legend class="admin-group-title" style="padding:0 6px;">CATEGORIE</legend>
                        <?php foreach ($opzioniCategorie as $opt): ?>
                            <label class="admin-group-title" style="display:flex;gap:4px;align-items:center;">
                                <input type="checkbox" name="cat[]" value="<?php echo (int)$opt['id_categoria']; ?>"<?php echo in_array((int)$opt['id_categoria'], $categorie, true) ? ' checked' : ''; ?>>
                                <?php echo htmlspecialchars(strtoupper($opt['descrizione_cat'] ?? '')); ?>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <div style="display:flex;gap:8px;align-items:center;margin-left:auto;">
                        <button type="submit" class="opzione-btn" style="padding:12px 20px;">MOSTRA</button>
                        <!-- Export PDF per-richiesta (#95): stessi filtri, niente file su disco -->
                        <a href="stat_pdf.php?<?php echo http_build_query($paramsFiltri + array('ord' => $ordCorrente, 'dir' => $dirCorrente)); ?>" class="opzione-btn" style="padding:12px 20px;text-decoration:none;"><svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 2v8m0 0L4.5 6.5M8 10l3.5-3.5M3 13.5h10"/></svg> SCARICA PDF</a>
                        <?php if (count($categorie) > 0): ?>
                            <a href="<?php echo htmlspecialchars(urlStat(array('giorno' => $giorno, 'vista' => $vistaCorrente))); ?>" class="opzione-btn" style="padding:12px 20px;text-decoration:none;">TUTTE</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </section>
        <!-- Fascia sotto (#113): titolo + tabella/grafico/pager della vista corrente -->
        <section class="admin-panel">
            <!-- Titolo sezione = vista corrente (#112): un solo h3 da $visteStat -->
            <h3 class="admin-group-title"><?php echo $visteStat[$vistaCorrente]; ?></h3>
            <?php if ($vistaCorrente === 'incasso'): ?>
            <!-- Incasso fine-giornata: il numero che comanda in fiera -->
            <table class="modifica-table">
                <thead><tr>
                    <th class="intestazione-tabella-descrizione">ORDINI</th>
                    <th class="intestazione-tabella-descrizione">PEZZI</th>
                    <th class="intestazione-tabella-descrizione">TOTALE</th>
                </tr></thead>
                <tbody><tr>
                    <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$incasso['ordini']; ?></td>
                    <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$incasso['pezzi']; ?></td>
                    <td class="cella-tabella-prezzo" style="font-size:16px;">&euro; <?php echo number_format((float)$incasso['totale'], 2, ',', '.'); ?></td>
                </tr></tbody>
            </table>
            <?php endif; ?>
            <?php if ($vistaCorrente === 'prodotto'): ?>
            <!-- Per-prodotto: quantita + totale per singolo prodotto -->
            <?php $svgDonut = stat_svg_donut($righeProdotto, (float)($incasso['totale'] ?? 0)); ?>
            <?php if ($svgDonut !== ''): ?>
                <div class="stat-donut">
                <?php echo $svgDonut; ?>
                <ul class="stat-legend">
                    <?php foreach ($righeProdotto as $i => $rigaLeg): ?>
                        <?php $tLeg = max(0.0, (float)($rigaLeg['totale'] ?? 0)); ?>
                        <?php if ($tLeg <= 0 || (float)($incasso['totale'] ?? 0) <= 0) continue; ?>
                        <li><span class="swatch" style="background:<?php echo htmlspecialchars(stat_colore($i)); ?>;"></span><?php echo htmlspecialchars(strtoupper($rigaLeg['prodotto'] ?? '')); ?> <?php echo number_format($tLeg / (float)$incasso['totale'] * 100, 1, ',', '.'); ?>% &middot; &euro; <?php echo number_format($tLeg, 2, ',', '.'); ?> &middot; QTA <?php echo (int)($rigaLeg['quantita'] ?? 0); ?></li>
                    <?php endforeach; ?>
                </ul>
                </div>
            <?php else: ?>
                <p class="stat-note">NESSUN DATO PER IL GRAFICO</p>
            <?php endif; ?>
            <table class="modifica-table">
                <thead><tr>
                    <?php foreach (array(array('prodotto', 'PRODOTTO'), array('quantita', 'QTA'), array('totale', 'TOTALE')) as $col): ?>
                        <?php $eOrdinata = ($ordCorrente === $col[0]); $prossimaDir = ($eOrdinata && $dirCorrente === 'ASC') ? 'DESC' : 'ASC'; ?>
                        <th class="intestazione-tabella-descrizione"><?php echo $col[1]; ?> <a href="<?php echo htmlspecialchars(urlStatVista($paramsFiltri, 'prodotto', $col[0], $prossimaDir, 1)); ?>" style="font-size:22px;text-decoration:none;"><?php echo $eOrdinata ? ($dirCorrente === 'ASC' ? '▲' : '▼') : '△'; ?></a></th>
                    <?php endforeach; ?>
                </tr></thead>
                <tbody>
                    <?php if (count($righePaginaStat) === 0): ?>
                        <tr><td class="cella-tabella-descrizione" style="font-size:16px;" colspan="3">NESSUNA RIGA</td></tr>
                    <?php endif; ?>
                    <?php foreach ($righePaginaStat as $riga): ?>
                        <tr>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['prodotto'] ?? '')); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['quantita']; ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;">&euro; <?php echo number_format((float)$riga['totale'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo stat_pager($paramsFiltri, 'prodotto', $ordCorrente, $dirCorrente, $paginaStat, $totPagineStat); ?>
            <?php endif; ?>
            <?php if ($vistaCorrente === 'fasce'): ?>
            <!-- Fasce: aggregazione oraria in ordine fiscale da ORA_CAMBIO_DATA -->
            <?php $svgAffluenza = stat_svg_affluenza($fasce, $oraCambio); ?>
            <?php if ($svgAffluenza !== ''): ?>
                <div class="stat-scroll"><?php echo $svgAffluenza; ?></div>
                <p class="stat-note">BARRE SCURE = ORDINI, BARRE CHIARE = PEZZI. SOLO FASCE CON MOVIMENTI, TABELLA 5 RIGHE PER PAGINA.</p>
            <?php else: ?>
                <p class="stat-note">NESSUN DATO PER IL GRAFICO</p>
            <?php endif; ?>
            <table class="modifica-table">
                <thead><tr>
                    <th class="intestazione-tabella-descrizione">ORA</th>
                    <th class="intestazione-tabella-descrizione">ORDINI</th>
                    <th class="intestazione-tabella-descrizione">PEZZI</th>
                    <th class="intestazione-tabella-descrizione">TOTALE</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($righePaginaStat as $f): ?>
                        <tr>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo sprintf('%02d:00', $f['ora']); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$f['ordini']; ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$f['pezzi']; ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;">&euro; <?php echo number_format((float)$f['totale'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo stat_pager($paramsFiltri, 'fasce', $ordCorrente, $dirCorrente, $paginaStat, $totPagineStat); ?>
            <?php endif; ?>
            <?php if ($vistaCorrente === 'giorni'): ?>
            <!-- Per-giorno: una riga per giorno di festa su DURATA_FESTA -->
            <table class="modifica-table">
                <thead><tr>
                    <th class="intestazione-tabella-descrizione">GIORNO</th>
                    <th class="intestazione-tabella-descrizione">ORDINI</th>
                    <th class="intestazione-tabella-descrizione">PEZZI</th>
                    <th class="intestazione-tabella-descrizione">TOTALE</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($righePaginaStat as $riga): ?>
                        <tr>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo htmlspecialchars($riga['giorno']); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['ordini']; ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['pezzi']; ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;">&euro; <?php echo number_format((float)$riga['totale'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo stat_pager($paramsFiltri, 'giorni', $ordCorrente, $dirCorrente, $paginaStat, $totPagineStat); ?>
            <?php endif; ?>
        </section>
    </main>
    <!-- Nav riservata: tab corrente evidenziata, resto come visualizza.php -->
    <aside>
        <h3 class="admin-group-title">TABELLA</h3>
        <nav style="flex-direction:column;align-items:stretch;">
            <button onclick="location.href='visualizza.php?tab=categorie'">CATEGORIE</button>
            <button onclick="location.href='visualizza.php?tab=prodotti'">PRODOTTI</button>
            <button onclick="location.href='visualizza.php?tab=prodotti_categorie'">POSIZIONI</button>
            <button onclick="location.href='visualizza.php?tab=contatori'">PRODOTTI VENDUTI</button>
            <button onclick="location.href='visualizza.php?tab=prodotti_contatori'">REGOLE VENDUTI</button>
            <hr style="border:0;border-top:2px solid #e7e9eb;margin:16px 0;">
            <button class="attivo">STATISTICHE</button>
            <!-- Sottoviste #111: stesso stile nav aside, solo padding inline ridotto (16px vs 24px, stessa pendenza var(--up), stesso cap 1.35x); padre sempre attivo, attiva sulla vista corrente -->
            <?php foreach ($visteStat as $chiaveVista => $etichettaVista): ?>
                <button onclick="location.href='<?php echo htmlspecialchars(urlStatVista($paramsFiltri, $chiaveVista, $ordCorrente, $dirCorrente, 1), ENT_QUOTES); ?>'"<?php echo ($chiaveVista === $vistaCorrente) ? ' class="attivo"' : ''; ?> style="margin-left:12px;padding-left:clamp(16px, calc(16px + var(--up)), 21.6px);padding-right:clamp(16px, calc(16px + var(--up)), 21.6px);"><?php echo $etichettaVista; ?></button>
            <?php endforeach; ?>
        </nav>
        <div class="admin-btn-row" style="margin-top:auto;"><a href="../index.php" class="opzione-btn">TORNA A SALSICCIA</a><a href="backup.php" class="opzione-btn">BACKUP</a><a href="visualizza.php?logout=1" class="opzione-btn">LOGOUT</a></div>
    </aside>
</body>
</html>
