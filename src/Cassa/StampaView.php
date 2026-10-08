<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// F2.1 #88: layout resto puro (corpo odierno di mostraLayoutResto(),
// functionsFrontend.inc:1335-1428, mappa §6 1386-1479).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 8 + §6 + §9 (sola lettura).
// Renderer statico sul modello CassaView/CatalogView (§7: niente template
// engine, niente DI container, niente interfacce singole).
// Solo echo da scalari: nessuna scrittura DB, nessuna emissione fiscale,
// mai $_GET/$_POST qui (solo scalari gia validati dal chiamante).
final class StampaView
{
    public static function resto(float $totale, float $pagato, string $metodo, string $base_url, bool $mostra_nuovo = true): void
    {
        $totale = (float)$totale;
        $pagato = (float)$pagato;
        // T26: $metodo validato via PayMethod enum; ignoto = '' (rifiutato).
        $pm = \Salsiccia\Cassa\PayMethod::tryFrom((string)$metodo);
        $metodo = $pm ? $pm->value : '';
        $resto = max(0, $pagato - $totale);

        $totale_fmt = number_format($totale, 2, ',', '.');
        $pagato_fmt = number_format($pagato, 2, ',', '.');
        $resto_fmt = number_format($resto, 2, ',', '.');

        echo '<header class="stampa-header">';
        echo '<h1>TOTALE ORDINE: &euro; ' . $totale_fmt . '</h1>';
        echo '</header>';

        echo '<section class="stampa-section">';

        //Banconote
        echo '<p class="gruppo-titolo"><span class="gruppo-titolo-icona">💶</span> BANCONOTE</p>';
        echo '<div class="banconote-grid">';
        echo '<div class="banconote-riga banconote-riga-1">';
        $banconote_1 = array(5, 10, 20, 50);
        foreach ($banconote_1 as $val) {
            $nuovo_pagato = $pagato + $val;
            $img_file = 'asset/soldi/' . $val . '_euro.png';
            echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $nuovo_pagato . '&metodo=' . $metodo . '\'" class="banconota-btn" style="background-image:url(\'' . $img_file . '\')">';
            echo '<span class="valore">' . $val . '€</span></button>';
        }
        echo '</div>';
        echo '<div class="banconote-riga banconote-riga-2">';
        $banconote_2 = array(100, 200, 500);
        foreach ($banconote_2 as $val) {
            $nuovo_pagato = $pagato + $val;
            $img_file = 'asset/soldi/' . $val . '_euro.png';
            echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $nuovo_pagato . '&metodo=' . $metodo . '\'" class="banconota-btn" style="background-image:url(\'' . $img_file . '\')">';
            echo '<span class="valore">' . $val . '€</span></button>';
        }
        echo '</div>';
        echo '</div>';

        //monete
        echo '<p class="gruppo-titolo"><span class="gruppo-titolo-icona">🪙</span> MONETE</p>';
        echo '<div class="monete-grid">';
        $monete = array(2, 1, 0.50, 0.20, 0.10, 0.05, 0.01);
        foreach ($monete as $val) {
            $nuovo_pagato = $pagato + $val;
            $label = $val >= 1 ? $val . '€' : ($val * 100) . 'c';
            $img_file = 'asset/soldi/' . ($val >= 1 ? $val : ($val * 100) . 'c') . '_euro.png';
            $classe_rame = ($val <= 0.05) ? ' moneta-rame' : '';
            echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $nuovo_pagato . '&metodo=' . $metodo . '\'" class="moneta-btn' . $classe_rame . '" style="background-image:url(\'' . $img_file . '\')">';
            echo '<span class="valore">' . $label . '</span></button>';
        }
        echo '</div>';

        //metodo pagamento (T26: valori da PayMethod enum, unica fonte)
        echo '<p class="metodo-titolo">STAMPA SCONTRINO FISCALE, METODO DI PAGAMENTO:</p>';
        echo '<div class="metodo-grid">';
        $attivo_contanti = ($metodo == \Salsiccia\Cassa\PayMethod::Contanti->value) ? ' attivo' : '';
        $attivo_carta = ($metodo == \Salsiccia\Cassa\PayMethod::Carta->value) ? ' attivo' : '';
        echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $pagato . '&metodo=' . \Salsiccia\Cassa\PayMethod::Contanti->value . '\'" class="metodo-btn' . $attivo_contanti . '">';
        echo '<span class="metodo-btn-icona">💵</span>';
        echo '<span class="metodo-btn-label">CONTANTI</span></button>';
        echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=' . $pagato . '&metodo=' . \Salsiccia\Cassa\PayMethod::Carta->value . '\'" class="metodo-btn' . $attivo_carta . '">';
        echo '<span class="metodo-btn-icona">💳</span>';
        echo '<span class="metodo-btn-label">CARTA DI CREDITO</span></button>';
        echo '</div>';

        if ($mostra_nuovo) {
            echo '<div style="text-align:center;"><button onclick="window.location.href=\'index.php\'" class="opzione-btn" style="padding:16px 40px;display:inline-flex;">NUOVO ORDINE</button></div>';
        }

        //Footer PAGATO-TOTALE-RESTO
        echo '<div class="stampa-footer">';
        echo '<button onclick="window.location.href=\'' . $base_url . '&pagato=0\'" class="stampa-close-btn">✕</button>';
        echo '<div class="stampa-footer-campo">';
        echo '<label>PAGATO</label>';
        echo '<input type="text" value="€ ' . $pagato_fmt . '" readonly>';
        echo '</div>';
        echo '<div class="stampa-footer-campo">';
        echo '<label>TOTALE</label>';
        echo '<input type="text" value="€ ' . $totale_fmt . '" readonly>';
        echo '</div>';
        echo '<div class="stampa-footer-campo">';
        echo '<label>RESTO</label>';
        echo '<input type="text" value="€ ' . $resto_fmt . '" readonly>';
        echo '</div>';
        echo '</div>';

        echo '</section>';
    }
}
