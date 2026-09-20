<?php

declare(strict_types=1);

namespace Salsiccia\Catalog;

// Template sottili del catalogo (T19): solo escaped-echo stile backoffice,
// zero accessi dati. I dati arrivano per parametro da CatalogRepo tramite
// le mostre deleganti in functionsFrontend.inc; HTML byte-identico ai
// rami originali.
final class CatalogView
{
    // Pulsanti categorie: $categorie = righe con id_categoria, colore,
    // descrizione_cat (stesso ordine del repo).
    public static function navCategorie(array $categorie, int $catAttivo): void
    {
        foreach ($categorie as $riga) {
            $attivo = ((int)$riga['id_categoria'] === $catAttivo) ? 'attivo' : '';
            $hex = \coloreCategoriaHex($riga['colore']);
            $stileAttivo = $attivo !== '' ? 'border:2px solid #2b3d4e;' : '';
            echo "<button class=\"nav-cat {$attivo}\" style=\"background-color:{$hex};{$stileAttivo}\" onclick=\"location.href='?cat=" . (int)$riga['id_categoria'] . "'\">" . htmlspecialchars((string)$riga['descrizione_cat'], ENT_QUOTES, 'UTF-8') . "</button>";
        }
    }

    // Titolo categoria: null => fallback storico 'SAKE'.
    public static function titolo(?string $descrizione): void
    {
        if ($descrizione !== null) {
            echo htmlspecialchars($descrizione, ENT_QUOTES, 'UTF-8');
        } else {
            echo 'SAKE';
        }
    }

    // Griglia prodotti: $prodotti = righe con posizione, prezzo,
    // descrizione_prod, id_prodotto; $categoriaVuota = riga categoria o
    // null per il ramo vuoto (tinta dal suo ['colore']).
    public static function tabellaProdotti(array $prodotti, $categoriaVuota, int $cat, int $butXRow, int $butXCol): void
    {
        $but_x_pag = $butXCol * $butXRow;
        $cat = (int)$cat;

        if (count($prodotti) === 0) {
            $colore = (is_array($categoriaVuota) && isset($categoriaVuota['colore'])) ? (string)$categoriaVuota['colore'] : '';
            $hexVuoto = \coloreCategoriaHex($colore);
            echo "<div style=\"background-color:{$hexVuoto}; height: 100%;\">";
            echo "<center><h1>NESSUN PRODOTTO IN QUESTA CATEGORIA</h1></center></div>";
            return;
        }

        $pos = 0;
        $riga = $prodotti[0];

        echo '<table><tbody><tr>';

        // griglia bottoni
        $control = $riga['posizione'];
        for ($i = 1; $i <= $but_x_pag; $i++) {
            if ($control == $i) {
                $prezzo = number_format((float)$riga['prezzo'], 2, ',', '.');
                $desc = htmlspecialchars(strtoupper((string)$riga['descrizione_prod']), ENT_QUOTES, 'UTF-8');
                $id_prodotto = (int)$riga['id_prodotto'];
                echo "<td class=\"td_bottone\">
                        <form method=\"post\" action=\"?cat=" . (int)$cat . "&action=a&id={$id_prodotto}\" style=\"display:contents;\">";
                \csrf_field();
                echo "<button type=\"submit\" class=\"bottone\">
                          {$desc}<br><b>&euro; {$prezzo}</b>
                        </button>
                        </form>
                      </td>";
                $pos++;
                if (isset($prodotti[$pos])) {
                    $riga = $prodotti[$pos];
                    $control = $riga['posizione'];
                }
            } else {
                echo '<td class="td_bottone">&nbsp;</td>';
            }
            // Vai a capo dopo but_x_col colonne
            if ($i % $butXCol == 0 && $i < $but_x_pag) {
                echo "</tr><tr>";
            }
        }
        echo '</tr></tbody></table>';
    }
}
