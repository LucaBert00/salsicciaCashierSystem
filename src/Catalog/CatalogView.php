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

    // Barra barcode kiosk (#46, da a49af04): form POST+CSRF (T14 come la
    // griglia prodotti, mai GET) + banner ignoto + listener wedge globale.
    // Pura echo: $cat e $barcodeErrore per parametro, zero query, zero
    // superglobali (T19/T30); errore gia escaped qui.
    public static function barraBarcode(int $cat, string $barcodeErrore = ''): void
    {
        $cat = (int)$cat;
        echo '<form method="post" action="?cat=' . $cat . '&action=b" id="form-barcode" style="display:flex;gap:8px;padding:8px 12px;" autocomplete="off">';
        \csrf_field();
        echo '<input type="text" name="bc" id="barcode-input" class="codice-text-field" maxlength="20" placeholder="SCANSIONA BARCODE..." value="" style="margin-bottom:0;text-align:left;padding-left:12px;" autofocus>';
        echo '<button type="submit" class="opzione-btn barcode-btn" style="white-space:nowrap;">OK</button>';
        echo '</form>';
        if ($barcodeErrore !== '') {
            echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:6px;border-radius:6px;margin:0 12px 8px;">BARCODE NON TROVATO: ' . htmlspecialchars($barcodeErrore, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        echo '<script>(function(){var i=document.getElementById("barcode-input");if(i&&!i.value)i.focus({preventScroll:true});'
            . 'var buf="",last=0;document.addEventListener("keydown",function(e){'
            . 'var t=document.activeElement;if(t&&(t.tagName==="INPUT"||t.tagName==="TEXTAREA"||t.tagName==="SELECT"))return;'
            . 'if(e.key==="Enter"){if(buf.length>=3){e.preventDefault();var f=document.getElementById("form-barcode");var inp=document.getElementById("barcode-input");if(f&&inp){inp.value=buf;f.submit();}buf="";}return;}'
            . 'if(e.key&&e.key.length===1){var n=Date.now();if(n-last>80)buf=e.key;else buf+=e.key;last=n;if(buf.length>20)buf=buf.slice(-20);}'
            . '});})();</script>';
    }
}
