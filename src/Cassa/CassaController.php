<?php

declare(strict_types=1);

namespace Salsiccia\Cassa;

// F1.2 #84: unico punto dove una richiesta diventa azione (assorbe
// gestisciAzioni() + i 12 boolean + la lista admin di public/index.php).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 5 + §4 + §3.2a-c (sola lettura).
// Solo colla HTTP: risolve la action via routes/cassa.php (unica fonte di
// verita F1.1), autorizza via campo auth, esegue gli handler esistenti
// cassa_azione_* (guard POST+CSRF invariati T14) e restituisce la view gia
// validata e autorizzata. Mai logica soldi (resta in OrderService), mai
// repository oltre la colla, mai HTML. Non ancora cablato (cablaggio in F1.4);
// la difesa in profondita dentro le singole mostra* resta dov'e.
final class CassaController
{
    /**
     * @param mixed $db mysqli reale o doppio di test con la stessa superficie
     */
    public static function gestisci($db, int $idCassa = 0): string
    {
        static $tabella = null;
        if ($tabella === null) {
            $tabella = (array) require dirname(__DIR__, 2) . '/routes/cassa.php';
        }
        // Unica lettura da $_GET: la action. Mai fatal (T23): non stringa o
        // assente = schermata principale, non listata = errore kiosk.
        $action = $_GET['action'] ?? '';
        if (!is_string($action) || $action === '') {
            return 'cassa';
        }
        if (!array_key_exists($action, $tabella)) {
            return 'azione-non-valida';
        }
        $riga = $tabella[$action];
        // Stesso redirect odierno public/index.php:36 (lista ora nel campo auth).
        if (($riga['auth'] ?? null) === 'admin' && empty($_SESSION['admin'])) {
            header('Location: index.php?action=c');
            return (string) ($tabella['c']['view'] ?? 'config');
        }
        // Handler null o ignoto = sola lettura; mai fatal se il nome non esiste
        // (switch_printer/info puntano a funzioni future, v. F1.1/F2).
        $handler = $riga['handler'] ?? null;
        if (is_string($handler) && $handler !== '' && function_exists($handler)) {
            $cassa = $idCassa;
            if ($cassa <= 0 && function_exists('cassaCorrente')) {
                $cassa = (int) cassaCorrente();
            }
            $handler($db, $cassa);
        }
        return (string) ($riga['view'] ?? 'cassa');
    }
}
