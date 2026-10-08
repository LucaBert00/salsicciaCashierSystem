<?php

declare(strict_types=1);

namespace Salsiccia\Printer;

use Salsiccia\Support\Env;
use Salsiccia\Support\Storage;

// Setup carta continua/singoli (F5.2 #104,
// docs/ARCHITETTURA_REVISTA.md §10 punto 18 + §6 166-279 + §9 target).
// Corpi verbatim da env.inc:166-279 (cartaStampaFilePerStampante,
// cartaStampaNormalizzaContenuto, cartaStampaLeggiSetup). Stessa mappa carta,
// stessa normalizzazione ZPL/EPL (q464/q832, ^PW464/^PW832, 8KB, no NUL),
// stessa lettura setup con &$errore esplicito. Gate via PrinterRegistry
// (stessa tabella di printer_gate_allowed, nessun duplicato).
// Metodi statici: nessun stato, autoload PSR-4 senza toccare composer.json.
// Transitorio onesto: env.inc resta dov'e con le funzioni originali ancora
// presenti (delete solo in F5.5); nessun consumer migrato qui (AdminView::carta,
// PrintService::inviaSetupCarta, CartaStampaF3Test invariati). Unica diff logica
// oltre a namespace/firma: path storage via Storage:: + log via Env::.
final class PaperSetup
{
    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $lang
     * @param mixed $continua
     */
    public static function filePerStampante($name, $conn, $lang, $continua): string|false
    {
        $name = (string)$name;
        $conn = (string)$conn;
        $lang = (string)$lang;
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            return false;
        }
        if (!PrinterRegistry::gateAllowed($name, $conn, $lang)) {
            return false;
        }
        if ($lang === 'EPL') {
            if ($continua) {
                if ($name === 'GX420t') {
                    return 'cmd_GX420t_CONTINUA_EPL';
                }
                if ($name === 'TLP2844') {
                    return 'cmd_TLP2844_CONTINUA_EPL';
                }
                if ($name === 'LP2844') {
                    return 'cmd_LP2844_CONTINUA_EPL';
                }
                return false;
            }
            if ($name === 'GX420t') {
                return 'cmd_GX420t_SINGOLI_EPL';
            }
            if ($name === 'TLP2844') {
                return 'cmd_TLP2844_SINGOLI_EPL';
            }
            if ($name === 'LP2844') {
                return 'cmd_LP2844_SINGOLI_EPL';
            }
            return false;
        }
        if ($lang === 'ZPL') {
            if ($name === 'ZD230') {
                return $continua ? 'cmd_ZD230_CONTINUA_ZPL' : 'cmd_ZD230_SINGOLI_ZPL';
            }
            if ($name === 'GX420t') {
                return $continua ? 'cmd_GX420t_CONTINUA_ZPL' : 'cmd_GX420t_SINGOLI_ZPL';
            }
            return false;
        }
        return false;
    }

    /**
     * @param mixed $raw
     * @param mixed $lang
     * @param mixed $continua
     */
    public static function normalizzaContenuto($raw, $lang, $continua): string|false
    {
        $raw = (string)$raw;
        if ($raw === '' || strlen($raw) > 8192 || strpos($raw, "\0") !== false) {
            return false;
        }
        if ($lang === 'EPL') {
            $larghezza = $continua ? 'q832' : 'q464';
            $norm = preg_replace('/^q\d+\s*$/m', $larghezza, $raw, 1);
            if ($norm === null) {
                return false;
            }
            if (preg_match('/^' . $larghezza . '\s*$/m', $norm) !== 1) {
                return false;
            }
            if (preg_match('/^Q\d+.*$/m', $norm) !== 1) {
                return false;
            }
            return $norm;
        }
        if ($lang === 'ZPL') {
            $larghezza = $continua ? '^PW832' : '^PW464';
            if (strpos($raw, '^XA') === false || strpos($raw, '^XZ') === false) {
                return false;
            }
            $norm = preg_replace('/\^PW\d+/', $larghezza, $raw, 1);
            if ($norm === null) {
                return false;
            }
            if (strpos($norm, $larghezza) === false) {
                return false;
            }
            return $norm;
        }
        return false;
    }

    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $lang
     * @param mixed $continua
     * @param mixed $baseDir
     */
    public static function leggiSetup($name, $conn, $lang, $continua, &$errore, $baseDir = ''): string|false
    {
        $errore = '';
        $file = self::filePerStampante($name, $conn, $lang, $continua);
        if ($file === false) {
            $errore = 'SETUP CARTA NON PREVISTO per ' . $name . ' - ' . $conn . ' - ' . $lang . ', modalita invariata.';
            return false;
        }
        if ($baseDir === '' || $baseDir === null) {
            $baseDir = Storage::path('printerCommand');
        }
        $percorso = (string)$baseDir . '/' . $file;
        if (!is_readable($percorso)) {
            $errore = 'FILE SETUP CARTA MANCANTE (' . $file . '), modalita invariata.';
            Env::log('error', 'carta_stampa: setup illeggibile ' . $percorso);
            return false;
        }
        $raw = @file_get_contents($percorso);
        if ($raw === false || $raw === '') {
            $errore = 'FILE SETUP CARTA ILLEGGIBILE (' . $file . '), modalita invariata.';
            Env::log('error', 'carta_stampa: setup illeggibile ' . $percorso);
            return false;
        }
        $norm = self::normalizzaContenuto($raw, (string)$lang, (bool)$continua);
        if ($norm === false) {
            $errore = 'FILE SETUP CARTA NON VALIDO (' . $file . '), modalita invariata.';
            Env::log('error', 'carta_stampa: setup non valido ' . $percorso);
            return false;
        }
        return $norm;
    }
}
