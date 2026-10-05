<?php

declare(strict_types=1);

namespace Salsiccia\Printer;

use Salsiccia\Support\Storage;

// Gate + cutter + registry + selezione persistita (F5.2 #104,
// docs/ARCHITETTURA_REVISTA.md §10 punto 18 + §6 139-158 e 281-347 + §9 target).
// Corpi verbatim da env.inc:139-158 (printer_gate_allowed, printer_is_continuous)
// + env.inc:281-347 (printer_has_cutter, printer_taglia_permesso,
// printer_known_printers, printer_selection_file, printer_leggi_selezione).
// Stesso gate, stesso cutter, stessa selezione persistita in storage/.
// Metodi statici: nessun stato, autoload PSR-4 senza toccare composer.json.
// Transitorio onesto: env.inc resta dov'e con le funzioni originali ancora
// presenti (delete solo in F5.5); nessun consumer migrato qui (PrintService,
// AdminView carta/switchPrinter, PrinterConfig as-is, CartaStampaF3Test e
// PrinterF4GateParserStatoTest invariati). Unica diff logica oltre a
// namespace/firma: path storage via Storage:: (stesso storage/ di env.inc).
final class PrinterRegistry
{
    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $lang
     */
    public static function gateAllowed($name, $conn, $lang): bool
    {
        if ($name === 'ZD230')
            return ($conn === 'DIRETTA' || $conn === 'RETE') && $lang === 'ZPL';
        // GX420t: solo DIRETTA, unica dual ZPL+EPL.
        if ($name === 'GX420t')
            return $conn === 'DIRETTA' && ($lang === 'ZPL' || $lang === 'EPL');
        if ($name === 'TLP2844' || $name === 'LP2844')
            return $conn === 'DIRETTA' && $lang === 'EPL';
        return false;
    }

    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $lang
     */
    public static function isContinuous($name, $conn, $lang): bool
    {
        return self::gateAllowed($name, $conn, $lang) && ($name === 'ZD230' || $name === 'GX420t');
    }

    /**
     * @param mixed $name
     */
    public static function hasCutter($name): bool
    {
        return $name === 'GX420t';
    }

    /**
     * @param mixed $name
     * @param mixed $conn
     * @param mixed $lang
     */
    public static function taglioPermesso($name, $conn, $lang): bool
    {
        return self::hasCutter($name) && self::isContinuous($name, $conn, $lang);
    }

    public static function knownPrinters(): array
    {
        return array(
            array('name' => 'ZD230', 'connection' => 'RETE', 'language' => 'ZPL'),
            array('name' => 'GX420t', 'connection' => 'DIRETTA', 'language' => 'ZPL'),
            array('name' => 'TLP2844', 'connection' => 'DIRETTA', 'language' => 'EPL'),
            array('name' => 'LP2844', 'connection' => 'DIRETTA', 'language' => 'EPL'),
        );
    }

    public static function selectionFile(): string
    {
        return Storage::path('stampante_selezione.json');
    }

    public static function leggiSelezione(): ?array
    {
        $f = self::selectionFile();
        $raw = is_readable($f) ? @file_get_contents($f) : false;
        if ($raw === false)
            return null;
        $d = json_decode($raw, true);
        if (!is_array($d) || empty($d['name']) || empty($d['connection']) || empty($d['language']))
            return null;
        $name = (string)$d['name'];
        $conn = (string)$d['connection'];
        $lang = (string)$d['language'];
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $name))
            return null;
        if (!self::gateAllowed($name, $conn, $lang))
            return null;
        $ip = isset($d['ip']) ? trim((string)$d['ip']) : '';
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP) === false)
            $ip = '';
        return array('name' => $name, 'connection' => $conn, 'language' => $lang, 'ip' => $ip);
    }
}
