<?php

declare(strict_types=1);

namespace Salsiccia\Printer;

// F2.3 #90: scrittura della selezione stampante (corpo odierno di
// impostaStampanteSelezionata(), functionsFrontend.inc:1159-1178, mappa §6
// 1214-1247). Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 10 + §6 + §9 + §5
// (sola lettura). Pura: mai echo/header/$_POST/$_GET/sessione/isAdmin
// (restano nel chiamante: mostraSwitchPrinter() oggi, AdminView in F2.5).
// Riuso as-is (mai spostati qui, sono F5): printer_gate_allowed(),
// printer_selection_file(), cassa_log(); per raggiungibile():
// printer_is_reachable(), printer_lpstat_stato(),
// printer_usb_locale_presente(), printer_stato_locale_ok().
// printer_cups_queue() resta in env.inc, riuso as-is altrove.
final class PrinterConfig
{
    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $lang
     * @param mixed $ip
     */
    public static function salva($name, $conn, $lang, $ip = ''): bool
    {
        $name = (string)$name;
        $conn = (string)$conn;
        $lang = (string)$lang;
        $ip = trim((string)$ip);
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name))
            return false;
        if (!printer_gate_allowed($name, $conn, $lang))
            return false;
        if ($ip !== '' && ($conn !== 'RETE' || filter_var($ip, FILTER_VALIDATE_IP) === false))
            return false;
        $d = array('name' => $name, 'connection' => $conn, 'language' => $lang, 'ip' => $ip);
        if (file_put_contents(printer_selection_file(), json_encode($d), LOCK_EX) === false)
        {
            cassa_log('error', "stampante_selezione: scrittura fallita");
            return false;
        }
        return true;
    }

    /**
     * @param mixed $nome
     * @param mixed $conn
     * @param mixed $ip
     */
    public static function raggiungibile($nome, $conn, $ip = ''): bool
    {
        if ($conn === 'RETE')
            return (bool)printer_is_reachable($nome, $conn, $ip);
        $st = printer_lpstat_stato($nome);
        $dev = isset($st['device']) ? (string)$st['device'] : '';
        $usb = stripos($dev, 'usb://') === 0 ? printer_usb_locale_presente($dev) : true;
        return (bool)printer_stato_locale_ok($st, $usb);
    }
}
