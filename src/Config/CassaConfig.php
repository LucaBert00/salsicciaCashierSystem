<?php

declare(strict_types=1);

namespace Salsiccia\Config;

use Salsiccia\Printer\PrinterRegistry;
use Salsiccia\Support\Env;
use Salsiccia\Support\Storage;

// Config cassa: costanti vive ex set.inc (F6.1 #109 + F6.3 #111,
// docs/ARCHITETTURA_REVISTA.md §10 punti 21-22 + §6 riga set.inc + §9 target
// + §4 CassaConfig::carica() + §7 file nuovi con declare(strict_types=1)).
// carica() legge config/cassa.php (defaults inerti) + override env dove
// set.inc li aveva (SALSICCIA_CONTINUOUS_LABEL, SALSICCIA_ORA_CAMBIO,
// SALSICCIA_PRINTER_IP). Fail-closed qui (F6.3 #111, stessi die 500 di
// set.inc, mai due fail-closed in parallelo). Stessa precedenza ex set.inc
// (env > selezione JSON > default; env > carta_stampa.mode > gate),
// stessi valori (smoke: byte-identici agli ex defined() di set.inc).
// Mai define(): i consumer leggono questo array (migrazione F6.2/F6.3,
// set.inc eliminato). Mai sniffing IP:
// ID_CASSA qui e' solo il default (1, ex ramo else di set.inc).
final class CassaConfig
{
    public static function carica(): array
    {
        Env::carica();
        // F6.3 #111: fail-closed ex set.inc (stesso 500, mai duplicato altrove).
        $adminHash = trim((string) getenv('SALSICCIA_ADMIN_PWD_HASH'));
        if ($adminHash === '' || password_get_info($adminHash)['algo'] === null) {
            http_response_code(500);
            die('Configurazione mancante: SALSICCIA_ADMIN_PWD_HASH non impostata o non valida. Genera con php -r "echo password_hash(\'CODICE\', PASSWORD_ARGON2ID), PHP_EOL;" e incolla l\'hash in .env.');
        }
        $cfg = dirname(__DIR__, 2) . '/config/cassa.php';
        $base = is_readable($cfg) ? include $cfg : array();
        if (!is_array($base)) {
            $base = array();
        }
        $out = $base;

        // Geometria bottoni: 7 righe solo a categoria singola (set.inc:129-132).
        $out['BOT_X_ROW'] = !empty($out['ONLY_ONE_CATEGORY']) ? 7 : 6;

        // Ora cambio data: env vince, fallback default (set.inc:120-124).
        $oraEnv = getenv('SALSICCIA_ORA_CAMBIO');
        if ($oraEnv !== false && trim((string) $oraEnv) !== '') {
            $out['ORA_CAMBIO_DATA'] = (string) $oraEnv;
        } else {
            $out['ORA_CAMBIO_DATA'] = isset($out['ORA_CAMBIO_DATA']) ? (string) $out['ORA_CAMBIO_DATA'] : '5';
        }

        // Stampante: env > selezione JSON > default (ex set.inc:60-90).
        $sel = PrinterRegistry::leggiSelezione();
        $envIp = trim((string) getenv('SALSICCIA_PRINTER_IP'));
        $selIp = (is_array($sel) && isset($sel['ip'])) ? trim((string) $sel['ip']) : '';
        $ip = $envIp !== '' ? $envIp : $selIp;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            if ($selIp !== '' && filter_var($selIp, FILTER_VALIDATE_IP) !== false) {
                $ip = $selIp;
            } else {
                $sel = null;
            }
        }
        // F6.3 #111: fail-closed ex set.inc (stesso 500, nessun fallback).
        if ($ip === '') {
            http_response_code(500);
            die('Configurazione mancante: SALSICCIA_PRINTER_IP non impostata. Copia .env.example in .env e compila i valori.');
        }
        if (is_array($sel)) {
            $out['PRINTER_NAME'] = (string) $sel['name'];
            $out['PRINTER_CONNECTION'] = (string) $sel['connection'];
            $out['PRINTER_LANGUAGE'] = (string) $sel['language'];
        } else {
            $out['PRINTER_NAME'] = isset($out['PRINTER_NAME']) ? (string) $out['PRINTER_NAME'] : 'ZD230';
            $out['PRINTER_CONNECTION'] = isset($out['PRINTER_CONNECTION'])
                ? (string) $out['PRINTER_CONNECTION']
                : 'RETE';
            $out['PRINTER_LANGUAGE'] = isset($out['PRINTER_LANGUAGE']) ? (string) $out['PRINTER_LANGUAGE'] : 'ZPL';
        }
        $out['PRINTER_IP'] = $ip;

        // Carta continua: env > carta_stampa.mode > gate (set.inc:100-112).
        $contEnv = getenv('SALSICCIA_CONTINUOUS_LABEL');
        if ($contEnv !== false && trim((string) $contEnv) !== '') {
            $out['CONTINUOUS_LABEL'] = ((int) $contEnv) ? 1 : 0;
        } else {
            $cartaFile = Storage::path('carta_stampa.mode');
            $cartaRaw = is_readable($cartaFile) ? @file_get_contents($cartaFile) : false;
            $cartaMode = $cartaRaw !== false ? trim((string) $cartaRaw) : '';
            if ($cartaMode === '1' || $cartaMode === '0') {
                $out['CONTINUOUS_LABEL'] = (int) $cartaMode;
            } else {
                $out['CONTINUOUS_LABEL'] = (int) PrinterRegistry::isContinuous(
                    isset($out['PRINTER_NAME']) ? $out['PRINTER_NAME'] : 'ZD230',
                    isset($out['PRINTER_CONNECTION']) ? $out['PRINTER_CONNECTION'] : 'RETE',
                    isset($out['PRINTER_LANGUAGE']) ? $out['PRINTER_LANGUAGE'] : 'ZPL'
                );
            }
        }

        // Path storage: stessi di set.inc:51-52 (config: nomi relativi inerti).
        $out['LABELS_FILE'] = Storage::path('label');
        $out['COMMAND_FILE'] = Storage::dir() . '/cmd/cmd';

        if (!isset($out['ID_CASSA'])) {
            $out['ID_CASSA'] = 1;
        }

        return $out;
    }
}
