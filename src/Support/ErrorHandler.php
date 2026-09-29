<?php

declare(strict_types=1);

namespace Salsiccia\Support;

// Gestori errore centralizzati cassa (T23).
// Rif. docs/ARCHITETTURA_REVISTA.md §4 (bootstrap.php: registrazione come primo
// passo esplicito del front controller) e §2 P1.
// Mappa #10: mai modificare docs/ARCHITETTURA_REVISTA.md (sola lettura).
// Interfaccia piccola: registra() nel bootstrap (public/index.php, prima di ogni
// require), mostraErrore() per la schermata. Nessuna dipendenza (DB/sessione
// possono essere la causa del guasto): solo echo + error_log con contesto, mai
// payload (niente code admin, niente importi).
final class ErrorHandler
{
    public static function registra(): void
    {
        static $registrato = false;
        if ($registrato) {
            return;
        }
        $registrato = true;
        set_exception_handler(function (\Throwable $e): void {
            $action = $_GET['action'] ?? '';
            error_log('cassa: eccezione non gestita [' . get_class($e) . '] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . ' action=' . (is_string($action) ? $action : ''));
            self::mostraErrore();
        });
        register_shutdown_function(function (): void {
            $err = error_get_last();
            if (!is_array($err)) {
                return;
            }
            if (!in_array($err['type'] ?? 0, array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
                return;
            }
            error_log('cassa: fatal [' . ($err['type'] ?? 0) . '] ' . ($err['message'] ?? '') . ' @ ' . ($err['file'] ?? '') . ':' . ($err['line'] ?? 0));
            self::mostraErrore();
        });
    }

    // Schermata errore kiosk: pagina intera se l'output non e partito (handler),
    // frammento se siamo gia dentro il layout (public/index.php). Mai doppio.
    public static function mostraErrore(?string $titolo = null): void
    {
        static $giaMostrato = false;
        if ($giaMostrato) {
            return;
        }
        $giaMostrato = true;
        $t = htmlspecialchars($titolo ?? 'ERRORE CASSA', ENT_QUOTES, 'UTF-8');
        $frammento = '<section style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex-grow:1;padding:40px;text-align:center;">'
            . '<p style="font-size:24px;font-weight:800;color:#e54b3c;margin-bottom:16px;">' . $t . '</p>'
            . '<p style="font-size:16px;color:#5a6b7c;margin-bottom:30px;">La vendita pu&ograve; proseguire: riprova o torna alla cassa.</p>'
            . '<a href="index.php" style="display:inline-block;padding:16px 40px;background:#2b3d4e;color:#fff;font-weight:800;text-decoration:none;border-radius:6px;">TORNA ALLA CASSA</a>'
            . '</section>';
        if (!headers_sent()) {
            http_response_code(500);
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Errore cassa</title></head>'
                . '<body style="margin:0;min-height:100vh;display:flex;font-family:sans-serif;background:#e7e9eb;">' . $frammento . '</body></html>';
            return;
        }
        echo $frammento;
    }
}
