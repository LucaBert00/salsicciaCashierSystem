<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// F2.4 #91: nucleo admin (corpi odierni di mostraPannelloAdmin()
// functionsFrontend.inc:657-708, mostraPannelloConfig():633-655,
// mostraContatori():943-995, mostraModificaInfo():1049-1091 e form di
// mostraRipristinaDb():1032-1044, mappa §6 657-732 + 967-1019 + 1056-1072
// + 1076-1127 + 1132-1146).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punti 9 (form in AdminView) e 11
// + §6 + §9 target + §3.2b + §7 (sola lettura).
// Renderer statico sul modello CassaView/CatalogView (§7: niente template
// engine, niente DI container, niente interfacce singole). Solo echo da
// dati gia pronti; stessi byte odierni (stessi link, stesso tastierino JS,
// stessa paginazione 5 righe, stessi campi festa, stessa form repair).
// Riuso as-is (mai spostati qui, restano dov sono): isFieraAttiva(),
// cassaCorrente(), contatori_totali() (lo sposta F4 punto 15),
// festa_leggi()/festa_imposta() su storage/festa.json (F0.3, mai
// file_put_contents su set.inc, congelato read-only), DbRepair::run()
// (F2.2, mai logica REPAIR duplicata), csrf_field()/csrf_ok() (T14),
// isAdmin() + redirect dentro i metodi (difesa in profondita §3.2b).
final class AdminView
{
    public static function pannello(): void
    {
        echo '<section class="admin-panel">';

        echo '<div>';
        echo '<h3 class="admin-group-title">GESTIONE</h3>';
        echo '<div class="admin-btn-row">';
        // ingresso diretto reserved/, contratto sessione
        echo '<a href="reserved/login.php" class="opzione-btn">GESTIONE PRODOTTI</a>';
        echo '<a href="?action=repair" class="opzione-btn">RIPRISTINA DB</a>';
        echo '<a href="?action=info" class="opzione-btn">MODIFICA FESTA</a>';
        echo '<form method="post" action="?action=fiera" onsubmit="return confirm(\'Cambiare MODALITA FIERA?\');" style="display:inline;">';
        \csrf_field();
        echo '<input type="hidden" name="val" value="' . (\isFieraAttiva() ? '0' : '1') . '">';
        echo '<button type="submit" class="opzione-btn">FIERA: ' . (\isFieraAttiva() ? 'ON' : 'OFF') . '</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';

        echo '<div>';
        echo '<h3 class="admin-group-title">CASSA TERMINALE: ' . \cassaCorrente() . ' (default da IP: ' . ID_CASSA . ')</h3>';
        echo '<form method="post" action="?action=cassa" class="admin-btn-row" style="align-items:center;">';
        \csrf_field();
        echo '<input type="number" name="id_cassa" value="' . \cassaCorrente() . '" min="1" max="999" class="codice-text-field" style="max-width:160px;">';
        echo '<button type="submit" class="opzione-btn">IMPOSTA CASSA</button>';
        echo '</form>';
        echo '</div>';

        echo '<div>';
        echo '<h3 class="admin-group-title">PRODOTTI VENDUTI</h3>';
        echo '<div class="admin-btn-row">';
        echo '<a href="?action=contatori" class="opzione-btn">CONTATORI</a>';
        echo '</div>';
        echo '</div>';

        echo '<div>';
        echo '<h3 class="admin-group-title">AMMINISTRAZIONE</h3>';
        echo '<div class="admin-btn-row">';
        echo '<a href="index.php" class="opzione-btn">CONTINUA ORDINE</a>';
        echo '<a href="?action=logout" class="opzione-btn">ESCI</a>';
        echo '</div>';
        echo '<div class="admin-btn-row" style="margin-top:clamp(6px, 0.9765625vmin, 12px);">';
        echo '<a href="?action=print_reset" class="opzione-btn">CAMBIA CARTA STAMPA: ' . ((defined('CONTINUOUS_LABEL') && CONTINUOUS_LABEL) ? 'CONTINUA' : 'SINGOLI') . '</a>';
        echo '<a href="?action=switch_printer" class="opzione-btn">CAMBIA STAMPANTE</a>';
        echo '<a href="?action=restart" class="opzione-btn danger">RIAVVIA</a>';
        echo '<a href="?action=shutdown" class="opzione-btn danger">SPEGNI</a>';
        echo '</div>';
        echo '</div>';

        echo '</section>';
    }

    public static function tastierino(): void
    {
        echo '<h3 style="font-size:13px;color:#5a6b7c;margin-bottom:12px;font-weight:800;">INSERISCI IL CODICE:</h3>';
        echo '<form method="post" action="?action=c&ok=1" id="form-admin-code">';
        echo '<input type="password" name="code" id="admin-code" class="codice-text-field" value="" readonly required>';

        echo '<div class="tastierino-grid">';
        for ($i = 1; $i <= 9; $i++)
            echo '<button type="button" class="tastierino-btn" data-kb-ch="' . $i . '">' . $i . '</button>';
        echo '<button type="button" class="tastierino-btn" data-kb-ch="*">*</button>';
        echo '<button type="button" class="tastierino-btn" data-kb-ch="0">0</button>';
        echo '<button type="button" class="tastierino-btn" data-kb-ch="@">@</button>';
        echo '</div>';

        echo '<div class="tastierino-actions">';
        echo '<button type="submit" class="tastierino-action-btn action-ok" style="display:flex;align-items:center;justify-content:center">OK</button>';
        echo '<button type="button" class="tastierino-action-btn action-canc" data-kb-canc style="display:flex;align-items:center;justify-content:center">CANC</button>';
        echo '</div>';

        echo '<a href="index.php" class="tastierino-close-btn" style="text-decoration:none;display:flex;align-items:center;justify-content:center">CHIUDI</a>';
        echo '</form>';
        echo '<script>(function(){var i=document.getElementById("admin-code");if(!i)return;var f=document.getElementById("form-admin-code");Array.prototype.forEach.call(f.querySelectorAll("[data-kb-ch]"),function(b){b.addEventListener("click",function(){i.value+=b.getAttribute("data-kb-ch");});});Array.prototype.forEach.call(f.querySelectorAll("[data-kb-canc]"),function(b){b.addEventListener("click",function(){i.value="";});});})();</script>';
    }

    /**
     * @param mixed $db mysqli reale o doppio di test con la stessa superficie
     */
    public static function contatori($db): void
    {
        if (!\isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }
        echo '<section class="admin-panel">';
        echo '<h3 class="admin-group-title">CONTATORI - PRODOTTI VENDUTI MONITORATI</h3>';
        $rpp = 5;
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pagina < 1)
            $pagina = 1;
        $righe = \contatori_totali($db);
        $tot = count($righe);
        $totPagine = max(1, (int)ceil($tot / $rpp));
        if ($pagina > $totPagine)
            $pagina = $totPagine;
        $offset = ($pagina - 1) * $rpp;
        if ($tot == 0)
        {
            echo '<p style="font-weight:800;text-align:center;color:#8c9ba5;">NESSUN PRODOTTO MONITORATO</p>';
        }
        else
        {
            echo '<table class="modifica-table">';
            echo '<thead><tr><th class="intestazione-tabella-descrizione">NOME PRODOTTO</th><th class="intestazione-tabella-quantita">QUANTITA VENDUTI</th></tr></thead>';
            echo '<tbody>';
            foreach (array_slice($righe, $offset, $rpp) as $c)
            {
                echo '<tr><td class="cella-tabella-descrizione">' . htmlspecialchars($c['nome'], ENT_QUOTES, 'UTF-8') . '</td><td class="cella-tabella-quantita">' . ($c['totale'] + 0) . '</td></tr>';
            }
            echo '</tbody>';
            echo '</table>';
            echo '<div class="admin-btn-row">';
            if ($pagina > 1)
                echo '<a href="?action=contatori&page=' . ($pagina - 1) . '" class="opzione-btn" style="padding:12px 20px;">&lt;</a>';
            foreach (array_unique(array(1, $pagina - 1, $pagina, $pagina + 1, $totPagine)) as $numPagina)
            {
                if ($numPagina < 1 || $numPagina > $totPagine)
                    continue;
                if ($numPagina == $pagina)
                    echo '<span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;">' . $numPagina . '</span>';
                else
                    echo '<a href="?action=contatori&page=' . $numPagina . '" class="opzione-btn" style="padding:12px 20px;">' . $numPagina . '</a>';
            }
            if ($pagina < $totPagine)
                echo '<a href="?action=contatori&page=' . ($pagina + 1) . '" class="opzione-btn" style="padding:12px 20px;">&gt;</a>';
            echo '</div>';
        }
        echo '<div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        echo '</section>';
    }

    public static function info(): void
    {
        if (!\isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }
        $letto = \festa_leggi();
        $eventName = isset($letto['event_name']) ? (string)$letto['event_name'] : '';
        $durataFesta = isset($letto['durata_festa']) ? (string)$letto['durata_festa'] : '1';

        $messaggio = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (!\csrf_ok())
            {
                $messaggio = 'TOKEN NON VALIDO.';
            }
            else
            {
            \festa_imposta(isset($_POST['EVENT_NAME']) ? $_POST['EVENT_NAME'] : '', isset($_POST['DURATA_FESTA']) ? $_POST['DURATA_FESTA'] : '');
            $letto = \festa_leggi();
            $eventName = isset($letto['event_name']) ? (string)$letto['event_name'] : '';
            $durataFesta = isset($letto['durata_festa']) ? (string)$letto['durata_festa'] : '1';
            $messaggio = 'Configurazione salvata con successo.';
            }
        }

        echo '<section class="admin-panel" style="align-items:center;text-align:center;">';
        echo '<h3 class="admin-group-title">CONFIGURAZIONE FESTA</h3>';
        if ($messaggio != '')
            echo '<p style="background:#5cb85c;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">' . $messaggio . '</p>';
        echo '<form method="post" action="?action=info" style="display:flex;flex-direction:column;gap:16px;max-width:560px;width:100%;margin:0 auto;align-items:center;text-align:center;">';
        \csrf_field();
        echo '<label style="font-weight:800;display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;width:100%;"><span style="font-size:18px;letter-spacing:1px;color:#2b3d4e;text-transform:uppercase;">NOME FESTA (max 32)</span><input type="text" name="EVENT_NAME" value="' . htmlspecialchars($eventName) . '" maxlength="32" class="codice-text-field"></label>';
        echo '<label style="font-weight:800;display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;width:100%;"><span style="font-size:18px;letter-spacing:1px;color:#2b3d4e;text-transform:uppercase;">DURATA (GIORNI)</span><input type="number" name="DURATA_FESTA" value="' . htmlspecialchars($durataFesta) . '" min="1" class="codice-text-field"></label>';
        echo '<div class="admin-btn-row">';
        echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
        echo '<button type="submit" class="opzione-btn">SALVA MODIFICHE</button>';
        echo '</div>';
        echo '</form>';
        echo '</section>';
    }

    /**
     * @param mixed $db mysqli reale o doppio di test con la stessa superficie
     */
    public static function repair($db): void
    {
        // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
        if (!\isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }

        echo '<section class="admin-panel">';
        echo '<h3 class="admin-group-title">RIPRISTINA DATABASE</h3>';
        if (isset($_POST['ripristina']))
        {
            if (!\csrf_ok())
            {
                echo '<p style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
                echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
            }
            else
            {
            $righe = \Salsiccia\System\DbRepair::run($db);
            foreach ($righe as $riga)
            {
                $table = (string)$riga['table'];
                if (!empty($riga['ok']))
                    echo "Tabella <b>$table</b> riparata.<br>";
                else
                    echo "Errore su <b>$table</b>: " . htmlspecialchars((string)$riga['error']) . "<br>";
            }
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
            }
        }
        else
        {
            echo '<p style="color:#e54b3c;font-weight:800;text-align:center;">ATTENZIONE: verr&agrave; eseguito il recupero di tutte le tabelle.</p>';
            echo '<form method="post" action="?action=repair" onsubmit="return confirm(\'Confermi il recupero di tutte le tabelle?\');">';
            \csrf_field();
            echo '<div class="admin-btn-row">';
            echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
            echo '<button type="submit" name="ripristina" value="1" class="opzione-btn">REPAIR DATABASE</button>';
            echo '</div>';
            echo '</form>';
        }
        echo '</section>';
    }
}
