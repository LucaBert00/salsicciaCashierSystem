<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

use Salsiccia\Catalog\CatalogRepo;
use Salsiccia\Support\ErrorHandler;

// F1.3 #85: renderer statico delle schermate cassa (assorbe i 14 elseif di
// public/index.php:53-115 + la query tinta di public/index.php:28-34).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 6 + §4 + §3.1-3.2 (sola lettura).
// Contratto: riceve un nome schermata gia validato e autorizzato da
// CassaController::gestisci() (viste di routes/cassa.php + 'cassa' +
// 'azione-non-valida') e produce HTML. Non legge $_GET/$_POST, non scrive su
// DB/filesystem rispetto a oggi. Modello: CatalogView (statico, §7: niente
// template engine, niente DI container). Le mostra*() restano in
// functionsFrontend.inc e vengono solo invocate con gli stessi argomenti di
// oggi; $cat e dimensioni bottoni arrivano per parametro esplicito, mai come
// globali ($mysqli/$cat/$but_x_row/$but_x_col di functionsFrontend.inc:64-65
// consumati in index.php:113 per contratto implicito §3.2d).
// Nomi vista -> ramo odierno: 'admin' = PANNELLO ADMIN, 'resto' (action 's') =
// schermata stampa, 'printReset' = CAMBIA CARTA STAMPA, 'switchPrinter' =
// CAMBIA STAMPANTE, 'config' = login (non admin) o PANNELLO ADMIN (admin, via
// isAdmin come il vecchio $showAdmin) + pannello config in aside,
// 'config-errore' = ERRORE CODICE DI AUTORIZZAZIONE ERRATO (il flag ok vive in
// $_GET che la vista non puo leggere: il cablaggio in F1.4 instrada il caso),
// default = cassa-main come il ramo else odierno.
// Nota F1 (fuori scope, F2 punto 8): mostraSchermataStampa() resta una
// vista-che-scrive (stampa su hardware, chiude l'ordine, emette lo scontrino
// fiscale); la separazione in StampaController e F2, qui viene solo invocata.
if (!class_exists(CatalogRepo::class)) {
    require_once __DIR__ . '/../Catalog/CatalogRepo.php';
}

final class CassaView
{
    /**
     * @param mixed $db mysqli reale o doppio di test con la stessa superficie
     */
    public static function render(string $view, $db, int $cat = 0, int $butXRow = 6, int $butXCol = 4): void
    {
        $cat = (int)$cat;
        // Stesso stato letto dal vecchio $showAdmin (index.php:15); la sessione
        // auth non e $_GET/$_POST e il controller ha gia autorizzato.
        $amministratore = function_exists('isAdmin') ? (bool)isAdmin() : false;
        // Tinta categoria come il vecchio $isCassaMain (index.php:26-34): solo
        // cassa-main e login, mai su admin/stampa/modifica/standby/errore.
        $conTinta = $view === 'cassa' || ($view === 'config' && !$amministratore);
        $hex = '#e7e9eb';
        if ($conTinta) {
            // T19: stessa riga categoria del ramo vuoto via CatalogRepo.
            $rigaCategoria = (new CatalogRepo($db))->categoria($cat);
            if (is_array($rigaCategoria) && isset($rigaCategoria['colore'])) {
                $hex = \coloreCategoriaHex($rigaCategoria['colore']);
            }
        }
        echo '<main' . ($conTinta ? ' class="cassa-c" style="--hex-cat:' . $hex . ';"' : '') . '>';
        match ($view) {
            'admin' => self::corpoAdmin($db, $cat),
            'resto' => self::corpoStampa($db, $cat),
            'modifica' => self::corpoModifica($db, $cat),
            'opzioni' => self::corpoOpzioni($db, $cat),
            'standby' => self::corpoStandby($db, $cat),
            'repair' => self::corpoRepair($db, $cat),
            'contatori' => self::corpoContatori($db, $cat),
            'printReset' => self::corpoPrintReset($db, $cat),
            'restart' => self::corpoRestart($db, $cat),
            'shutdown' => self::corpoShutdown($db, $cat),
            'switchPrinter' => self::corpoSwitchPrinter($db, $cat),
            'info' => self::corpoInfo($db, $cat),
            'config' => $amministratore ? self::corpoAdmin($db, $cat) : self::corpoCassa($db, $cat, $butXRow, $butXCol),
            'config-errore' => self::corpoErroreCodice(),
            'azione-non-valida' => ErrorHandler::mostraErrore('AZIONE NON VALIDA'),
            default => self::corpoCassa($db, $cat, $butXRow, $butXCol),
        };
        echo '</main>';
        self::fianco($view, $db, $amministratore);
    }

    // <nav> identico ai 12 rami odierni (stessi byte, nessuna spaziatura extra).
    private static function barraNavigazione($db, int $cat): void
    {
        echo '<nav>';
        mostraNavCategorie($db, $cat);
        mostraIndicatoreTipo($db);
        echo '</nav>';
    }

    private static function corpoCassa($db, int $cat, int $butXRow, int $butXCol): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>';
        mostraTitolo($db, $cat);
        echo '</h1></header>';
        mostraBarraBarcode($db, $cat);
        echo '<section id="main">';
        mostraTabellaProdotti($db, $cat, $butXRow, $butXCol);
        echo '</section>';
        echo '<div class="footer">';
        mostraFooterBottoni($cat);
        echo '</div>';
    }

    private static function corpoAdmin($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>PANNELLO ADMIN</h1></header>';
        mostraPannelloAdmin();
    }

    private static function corpoStampa($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        mostraSchermataStampa($db);
    }

    private static function corpoModifica($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>MODIFICA ORDINE</h1></header>';
        mostraModificaOrdine($db);
        echo '<div class="footer">';
        mostraFooterModifica($cat);
        echo '</div>';
    }

    private static function corpoOpzioni($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>TIPOLOGIA ORDINE</h1></header>';
        mostraOpzioni();
        echo '<div class="footer">';
        mostraFooterModifica($cat);
        echo '</div>';
    }

    private static function corpoStandby($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>ORDINI IN STANDBY</h1></header>';
        mostraOrdiniStandby($db);
    }

    private static function corpoRepair($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>RIPRISTINA DB</h1></header>';
        mostraRipristinaDb($db);
    }

    private static function corpoContatori($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>CONTATORI</h1></header>';
        mostraContatori($db);
    }

    private static function corpoPrintReset($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>CAMBIA CARTA STAMPA</h1></header>';
        mostraPrintReset();
    }

    private static function corpoRestart($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>RIAVVIA</h1></header>';
        mostraRestart();
    }

    private static function corpoShutdown($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>SPEGNI</h1></header>';
        mostraShutdown();
    }

    private static function corpoSwitchPrinter($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>CAMBIA STAMPANTE</h1></header>';
        mostraSwitchPrinter();
    }

    private static function corpoInfo($db, int $cat): void
    {
        self::barraNavigazione($db, $cat);
        echo '<header><h1>MODIFICA FESTA</h1></header>';
        mostraModificaInfo();
    }

    private static function corpoErroreCodice(): void
    {
        echo '<section style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex-grow:1;">'
            . '<p style="font-size:24px;font-weight:800;color:#e54b3c;margin-bottom:30px;">ERRORE CODICE DI AUTORIZZAZIONE ERRATO</p>'
            . '</section>';
    }

    // <aside> odierno (index.php:117-132): testata riepilogo sempre visibile,
    // pannello config solo su schermata login/errore non admin, resto invariato.
    private static function fianco(string $view, $db, bool $amministratore): void
    {
        echo '<aside><header><h2>RIEPILOGO ORDINE</h2><div class="order-summary-box">';
        mostraBoxRiepilogo($db);
        echo '</div><div class="aside-actions">';
        mostraAzioniLaterali();
        echo '</div></header>';
        if (($view === 'config' || $view === 'config-errore') && !$amministratore) {
            echo '<section style="flex-grow:1;">';
            mostraPannelloConfig();
            echo '</section>';
        } else {
            echo '<section><h3>RIEPILOGO PRODOTTI</h3><ul class="cart-list">';
            mostraListaProdotti($db);
            echo '</ul></section>';
        }
        mostraAzioniExtra();
        echo '</aside>';
    }
}
