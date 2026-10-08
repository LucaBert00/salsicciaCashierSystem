<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// F2.4 #91: nucleo admin (corpi odierni di mostraPannelloAdmin()
// functionsFrontend.inc:657-708, mostraPannelloConfig():633-655,
// mostraContatori():943-995, mostraModificaInfo():1049-1091 e form di
// mostraRipristinaDb():1032-1044, mappa §6 657-732 + 967-1019 + 1056-1072
// + 1076-1127 + 1132-1146).
// F2.5 #92: periferiche + power (corpi odierni di mostraPrintReset()
// :916-972, mostraSwitchPrinter():1027-1151, mostraPower/Restart/Shutdown/
// Riscontro/Diagnostica:785-879, mappa §6 912-963 + 1152-1213 + 1256-1380).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punti 9 (form in AdminView) e 11
// + §6 + §9 target + §3.2b + §7 (sola lettura).
// Renderer statico sul modello CassaView/CatalogView (§7: niente template
// engine, niente DI container, niente interfacce singole). Solo echo da
// dati gia pronti; stessi byte odierni (stessi link, stesso tastierino JS,
// stessa paginazione 5 righe, stessi campi festa, stessa form repair,
// stesse card/grid/pill switch, stessi 4 esiti carta, stessi messaggi
// power TOKEN/COMANDO NON AVVIATO/SCHEDULATO + riga coda DIRETTA/RETE).
// Riuso diretto dei moduli F5: isFieraAttiva(),
// cassaCorrente(), StatsData::contatori_totali() (F4.2 punto 15, #99),
// CassaFlags::festaLeggi()/festaImposta() su storage/festa.json (F0.3, mai
// file_put_contents su set.inc, congelato read-only), DbRepair::run()
// (F2.2, mai logica REPAIR duplicata), csrf_field()/csrf_ok() (T14),
// isAdmin() + redirect dentro i metodi (difesa in profondita §3.2b),
// PaperSetup::leggiSetup()/impostaCartaStampa()/PrinterRegistry::
// knownPrinters()/CupsState::cupsQueue()/PRINTER_* (F5, qui solo riuso,
// mai duplicare),
// PrinterConfig::salva()/raggiungibile() (F2.3), PrintService::
// inviaSetupCarta() (F3.2), Power::* (F2.5, logica sudo/marker/uptime).
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
        $righe = \Salsiccia\Stats\StatsData::contatori_totali($db);
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
        $letto = \Salsiccia\Config\CassaFlags::festaLeggi();
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
            \Salsiccia\Config\CassaFlags::festaImposta(isset($_POST['EVENT_NAME']) ? $_POST['EVENT_NAME'] : '', isset($_POST['DURATA_FESTA']) ? $_POST['DURATA_FESTA'] : '');
        $letto = \Salsiccia\Config\CassaFlags::festaLeggi();
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

    public static function carta(): void
    {
        // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
        if (!\isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }
        $modo = (defined('CONTINUOUS_LABEL') && CONTINUOUS_LABEL) ? 'CONTINUA' : 'SINGOLI';
        $nuovo = (defined('CONTINUOUS_LABEL') && CONTINUOUS_LABEL) ? '0' : '1';
        $modoNuovo = $nuovo === '1' ? 'CONTINUA' : 'SINGOLI';
        echo '<section class="admin-panel">';
        echo '<h3 class="admin-group-title admin-msg-big">CAMBIA CARTA STAMPA - CARTA ATTUALE: ' . $modo . '</h3>';
        if (isset($_POST['cambia_carta']))
        {
            if (!\csrf_ok())
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
                echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
            }
            else
            {
            $continuaNuova = ($nuovo === '1');
            $erroreSetup = '';
            $setup = \Salsiccia\Printer\PaperSetup::leggiSetup(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE, $continuaNuova, $erroreSetup, \Salsiccia\Support\Storage::path('printerCommand'));
            if ($setup === false)
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">' . htmlspecialchars($erroreSetup, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            elseif (!\Salsiccia\Cassa\PrintService::inviaSetupCarta($setup))
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">INVIO SETUP CARTA ALLA STAMPANTE FALLITO, modalita invariata.</p>';
            }
            elseif (\impostaCartaStampa($nuovo))
            {
                echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">Carta commutata a ' . $modoNuovo . ': setup stampante applicato, la prossima stampa usa la nuova modalita.</p>';
            }
            else
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;font-weight:800;text-align:center;padding:12px;border-radius:6px;">SETUP STAMPANTE APPLICATO MA SCRITTURA STATO CARTA FALLITA: riallineare al prossimo toggle.</p>';
            }
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
            }
        }
        else
        {
            echo '<p class="admin-msg-big" style="color:#e54b3c;">ATTENZIONE: dopo aver cambiato fisicamente la carta, la modalita di stampa passera da ' . $modo . ' a ' . $modoNuovo . '.</p>';
            echo '<form method="post" action="?action=print_reset" onsubmit="return confirm(\'Cambiare carta di stampa in ' . $modoNuovo . '?\');">';
            \csrf_field();
            echo '<div class="admin-btn-row">';
            echo '<a href="index.php" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
            echo '<button type="submit" name="cambia_carta" value="1" class="opzione-btn">CAMBIA IN ' . $modoNuovo . '</button>';
            echo '</div>';
            echo '</form>';
        }
        echo '</section>';
    }

    public static function switchPrinter(): void
    {
        // difesa in profondita, index.php filtra gia via isAdmin+redirect; mai GET anonimo.
        if (!\isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }
        echo '<section class="admin-panel switch-printer-panel">';
        echo '<h3 class="admin-group-title admin-msg-big">CAMBIA STAMPANTE - ATTUALE: ' . htmlspecialchars(PRINTER_NAME . ' - ' . PRINTER_CONNECTION . ' - ' . PRINTER_LANGUAGE, ENT_QUOTES, 'UTF-8') . '</h3>';
        if (isset($_POST['cambia_stampante']))
        {
            if (!\csrf_ok())
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
            }
            else
            {
                $nome = (string)($_POST['nome'] ?? '');
                $conn = (string)($_POST['conn'] ?? '');
                $lingua = (string)($_POST['lingua'] ?? '');
                $ip = $conn === 'RETE' ? trim((string)($_POST['ip'] ?? '')) : '';
                // riuso reachability F4.2; ip POST o fallback PRINTER_IP come in lista
                $ipCheck = $conn === 'RETE' ? ($ip !== '' ? $ip : (defined('PRINTER_IP') ? PRINTER_IP : '')) : '';
                if (!\Salsiccia\Printer\PrinterConfig::raggiungibile($nome, $conn, $ipCheck))
                {
                    echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">STAMPANTE NON RAGGIUNGIBILE (pallino rosso), selezione non cambiata.</p>';
                }
                elseif (\Salsiccia\Printer\PrinterConfig::salva($nome, $conn, $lingua, $ip))
                {
                    echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;padding:12px;border-radius:6px;">Stampante commutata a ' . htmlspecialchars($nome . ' - ' . $conn . ' - ' . $lingua, ENT_QUOTES, 'UTF-8') . ': la prossima stampa usa la nuova selezione.</p>';
                }
                else
                {
                    echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">SELEZIONE NON VALIDA (gate), stampante invariata.</p>';
                }
            }
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
        else
        {
            echo '<script>function selModo(r){var f=r.form,b=f.cambia_stampante;if(b){if(b.getAttribute("data-unreach")==="1")return;b.disabled=false;b.style.opacity="";b.style.cursor="";}var L=f.querySelectorAll("label.opzione-btn");for(var i=0;i<L.length;i++){L[i].removeAttribute("style");}var l=r.parentNode;l.style.border="2px solid #2b3d4e";l.style.background="#2b3d4e";l.style.color="#fff";}</script>';
            $raggiungibili = array();
            $altre = array();
            foreach (\Salsiccia\Printer\PrinterRegistry::knownPrinters() as $p)
            {
                $p['ok'] = \Salsiccia\Printer\PrinterConfig::raggiungibile((string)$p['name'], (string)$p['connection'], defined('PRINTER_IP') ? PRINTER_IP : '');
                if ($p['ok'])
                    $raggiungibili[] = $p;
                else
                    $altre[] = $p;
            }
            $ordinate = array_merge($raggiungibili, $altre);
            $rpp = 4;
            $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($pagina < 1)
                $pagina = 1;
            $totPagine = max(1, (int)ceil(count($ordinate) / $rpp));
            if ($pagina > $totPagine)
                $pagina = $totPagine;
            $visibili = array_slice($ordinate, ($pagina - 1) * $rpp, $rpp);
            echo '<div class="switch-printer-grid">';
            foreach ($visibili as $p)
            {
                $nome = (string)$p['name'];
                $conn = (string)$p['connection'];
                $ok = !empty($p['ok']);
                $attuale = ($nome === PRINTER_NAME && $conn === PRINTER_CONNECTION);
                echo '<div class="switch-printer-card">';
                echo '<p class="admin-msg-big" style="color:#2b3d4e;margin:0 0 4px;"><span title="' . ($ok ? 'Raggiungibile' : 'Non raggiungibile') . '" style="color:' . ($ok ? '#5cb85c' : '#d9534f') . ';">&#9679;</span> ' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ($attuale ? ' <span style="display:inline-flex;align-items:center;background:#5cb85c;color:#fff;font-weight:800;border-radius:12px;padding:2px 12px;font-size:14px;">ATTUALE</span>' : '') . '</p>';
                echo '<p class="admin-msg-big" style="color:#8c9ba5;margin:0 0 8px;">CONNESSIONE: ' . htmlspecialchars($conn, ENT_QUOTES, 'UTF-8') . '</p>';
                echo '<div class="admin-btn-row" style="align-items:center;">';
                if ($nome === 'GX420t')
                {
                    echo '<span class="admin-msg-big" style="color:#8c9ba5;">MODALITA STAMPA:</span>';
                    echo '<form method="post" action="?action=switch_printer" onsubmit="return confirm(\'Cambiare stampante?\');" style="display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:center;">';
                    \csrf_field();
                    echo '<input type="hidden" name="nome" value="GX420t">';
                    echo '<input type="hidden" name="conn" value="DIRETTA">';
                    foreach (array('ZPL', 'EPL') as $lingua)
                    {
                        $sel = ($attuale && PRINTER_LANGUAGE === $lingua);
                        echo '<label class="opzione-btn"' . ($sel ? ' style="border:2px solid #2b3d4e;background:#2b3d4e;color:#fff;"' : '') . '><input type="radio" name="lingua" value="' . $lingua . '"' . ($sel ? ' checked' : '') . ($ok ? '' : ' disabled') . ' onchange="selModo(this)" style="display:none;"> ' . $lingua . '</label>';
                    }
                    echo '<button type="submit" name="cambia_stampante" value="1" class="opzione-btn"' . ((!$ok || !$attuale) ? ' disabled style="opacity:.45;cursor:not-allowed;"' : '') . (!$ok ? ' data-unreach="1"' : '') . '>SELEZIONA</button>';
                    echo '</form>';
                }
                else
                {
                    $lingua = (string)$p['language'];
                    echo '<span class="admin-msg-big" style="color:#8c9ba5;">MODALITA STAMPA:</span>';
                    echo '<span class="opzione-btn" style="border:2px solid #2b3d4e;cursor:default;">' . htmlspecialchars($lingua, ENT_QUOTES, 'UTF-8') . '</span>';
                    echo '<form method="post" action="?action=switch_printer" onsubmit="return confirm(\'Cambiare stampante?\');" style="display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:center;">';
                    \csrf_field();
                    echo '<input type="hidden" name="nome" value="' . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . '">';
                    echo '<input type="hidden" name="conn" value="' . htmlspecialchars($conn, ENT_QUOTES, 'UTF-8') . '">';
                    echo '<input type="hidden" name="lingua" value="' . htmlspecialchars($lingua, ENT_QUOTES, 'UTF-8') . '">';
                    if ($conn === 'RETE')
                        echo '<input type="hidden" name="ip" value="' . htmlspecialchars(defined('PRINTER_IP') ? PRINTER_IP : '', ENT_QUOTES, 'UTF-8') . '">';
                    echo '<button type="submit" name="cambia_stampante" value="1" class="opzione-btn"' . ($ok ? '' : ' disabled style="opacity:.45;cursor:not-allowed;"') . '>SELEZIONA</button>';
                    echo '</form>';
                }
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            echo '<div class="admin-btn-row">';
            if ($pagina > 1)
                echo '<a href="?action=switch_printer&page=' . ($pagina - 1) . '" class="opzione-btn" style="padding:12px 20px;">&lt;</a>';
            foreach (array_unique(array(1, $pagina - 1, $pagina, $pagina + 1, $totPagine)) as $numPagina)
            {
                if ($numPagina < 1 || $numPagina > $totPagine)
                    continue;
                if ($numPagina == $pagina)
                    echo '<span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;">' . $numPagina . '</span>';
                else
                    echo '<a href="?action=switch_printer&page=' . $numPagina . '" class="opzione-btn" style="padding:12px 20px;">' . $numPagina . '</a>';
            }
            if ($pagina < $totPagine)
                echo '<a href="?action=switch_printer&page=' . ($pagina + 1) . '" class="opzione-btn" style="padding:12px 20px;">&gt;</a>';
            echo '</div>';
            echo '<div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
        echo '</section>';
    }

    public static function riscontroPower($mode): void
    {
        $f = \Salsiccia\System\Power::markerPowerFile();
        $raw = is_readable($f) ? @file_get_contents($f) : false;
        if ($raw === false || trim((string)$raw) === '')
            return;
        $d = json_decode((string)$raw, true);
        if (!is_array($d) || empty($d['t']) || !isset($d['mode']) || $d['mode'] !== $mode)
        {
            if (is_array($d))
                return;
            @unlink($f);
            return;
        }
        $esito = \Salsiccia\System\Power::esitoTentativoPower((int)$d['t'], time(), \Salsiccia\System\Power::uptimeMacchina());
        $quando = date('d/m H:i:s', (int)$d['t']);
        if ($esito === 'riuscito')
        {
            echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;padding:12px;border-radius:6px;">COMANDO RIUSCITO: cassa riavviata/riaccesa dopo invio delle ' . $quando . '.</p>';
            @unlink($f);
        }
        elseif ($esito === 'fallito')
        {
            echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">COMANDO FALLITO: invio delle ' . $quando . ' senza effetto, cassa mai spenta. Verifica sudoers/systemd (vedi DIAGNOSTICA sotto).</p>';
            @unlink($f);
        }
        else
        {
            echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">COMANDO IN ATTESA: invio delle ' . $quando . ', la cassa dovrebbe spegnersi a momenti. Ricarica tra un minuto per il riscontro.</p>';
        }
    }

    public static function diagnosticaPower($mode): void
    {
        $who = array();
        @exec('whoami 2>/dev/null', $who);
        $utente = !empty($who[0]) ? trim((string)$who[0]) : '?';
        $cmd = \Salsiccia\System\Power::scegliComandoAlimentazione($mode);
        $up = \Salsiccia\System\Power::uptimeMacchina();
        echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">DIAGNOSTICA: utente=' . htmlspecialchars($utente, ENT_QUOTES, 'UTF-8') . ' os=' . PHP_OS_FAMILY . ' cmd=' . ($cmd !== '' ? htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8') : 'NESSUNO (permessi?)') . ' systemd=' . (is_dir('/run/systemd/system') ? 'si' : 'no') . ' uptime=' . ($up !== null ? (int)$up . 's' : 'n/d') . '</p>';
    }

    public static function power($mode, $action, $field, $titolo, $msgConferma, $labelBtn, $msgOk): void
    {
        if (!\isAdmin())
        {
            header("Location: index.php?action=c");
            exit;
        }
        echo '<section class="admin-panel">';
        echo '<h3 class="admin-group-title admin-msg-big">' . $titolo . '</h3>';
        self::riscontroPower($mode);
        if (isset($_POST[$field]))
        {
            if (!\csrf_ok())
            {
                echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">TOKEN NON VALIDO.</p>';
            }
            else
            {
                $ok = \Salsiccia\System\Power::schedulaAzioneAlimentazione($mode);
                if ($ok)
                    echo '<p class="admin-msg-big" style="background:#5cb85c;color:#fff;padding:12px;border-radius:6px;">' . $msgOk . '</p>';
                else
                    echo '<p class="admin-msg-big" style="background:#d9534f;color:#fff;padding:12px;border-radius:6px;">COMANDO NON AVVIATO: verifica sudoers/systemd sul kiosk.</p>';
                echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">Coda stampa: DIRETTA cancellata (best-effort), RETE nessun job pendente (FTP sincrono verso ' . htmlspecialchars(PRINTER_IP, ENT_QUOTES, 'UTF-8') . '). Se nulla accade: utente web senza NOPASSWD su /sbin/shutdown o host senza systemd.</p>';
            }
            echo '<br><div class="admin-btn-row"><a href="index.php" class="opzione-btn" style="text-decoration:none;">TORNA</a></div>';
        }
        else
        {
            echo '<p class="admin-msg-big" style="color:#e54b3c;">ATTENZIONE: ' . $msgConferma . '</p>';
            echo '<p class="admin-msg-big" style="color:#2b3d4e;font-weight:600;">Coda stampa: DIRETTA verra svuotata, RETE nessun job pendente (invio FTP sincrono).</p>';
            echo '<form method="post" action="?action=' . $action . '" onsubmit="return confirm(\'Confermi?\');">';
            \csrf_field();
            echo '<div class="admin-btn-row">';
            echo '<a href="index.php?action=c&ok=1" class="opzione-btn" style="text-decoration:none;">ANNULLA</a>';
            echo '<button type="submit" name="' . $field . '" value="1" class="opzione-btn danger">' . $labelBtn . '</button>';
            echo '</div>';
            echo '</form>';
        }
        self::diagnosticaPower($mode);
        echo '</section>';
    }

    public static function restart(): void
    {
        self::power('reboot', 'restart', 'restart', 'RIAVVIA TERMINALE', 'il terminale cassa verra riavviato.', 'RIAVVIA ORA', 'RIAVVIO SCHEDULATO: il terminale si riavvia tra pochi secondi.');
    }

    public static function shutdown(): void
    {
        self::power('poweroff', 'shutdown', 'shutdown', 'SPEGNI TERMINALE', 'il terminale cassa verra spento.', 'SPEGNI ORA', 'SPEGNIMENTO SCHEDULATO: il terminale si spegne tra pochi secondi.');
    }
}
