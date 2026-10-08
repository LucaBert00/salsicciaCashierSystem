<?php

declare(strict_types=1);

namespace Salsiccia\System;

// F2.5 #92: pure/operative alimentazione (corpi odierni di
// candidatiAlimentazione()/escapaComando()/scegliComandoAlimentazione()/
// markerPowerFile()/uptimeMacchina()/esitoTentativoPower()/scriviMarkerPower()/
// svuotaCodaStampaPreSpegnimento()/schedulaAzioneAlimentazione(),
// functionsFrontend.inc:739-863, mappa §6 739-910).
// Rif. docs/ARCHITETTURA_REVISTA.md §10 punto 11b + §6 + §9 target + §7
// (integrazione hardware/OS: classe con metodi statici).
// Corpi verbatim invariati: legacy-first systemctl poi shutdown con
// is_executable + fallback command -v, escapeshellarg per pezzo, primo
// permesso via sudo -n -l sul comando esatto (mai verde falso), marker via
// Storage::path('power_attempt.json') mai docroot, uptime da
// /proc/uptime, esito riuscito/fallito/attesa soglia 180s, coda DIRETTA
// cancel -a best-effort mai bloccante con whitelist + CupsState::cupsQueue
// as-is (RETE nessun job, invio sincrono), schedulazione sleep 2 + sudo in
// background con system() rc.
// Riuso diretto dei moduli F5/F6: Storage::path(), Env::log(),
// CupsState::cupsQueue(), CassaConfig::carica().
final class Power
{
    public static function candidatiAlimentazione($mode): array
    {
        $aSys = ($mode === 'poweroff') ? 'poweroff' : 'reboot';
        $aShut = ($mode === 'poweroff') ? '-h now' : '-r now';
        $c = array();
        foreach (array('/bin/systemctl', '/usr/bin/systemctl') as $s)
            if (is_executable($s))
                $c[] = $s . ' ' . $aSys;
        foreach (array('/sbin/shutdown', '/usr/sbin/shutdown') as $s)
            if (is_executable($s))
                $c[] = $s . ' ' . $aShut;
        if (empty($c))
        {
            $out = array();
            @exec('command -v systemctl shutdown 2>/dev/null', $out);
            foreach ($out as $r)
            {
                $r = trim((string)$r);
                if ($r === '' || !is_executable($r))
                    continue;
                $c[] = $r . (substr($r, -10) === 'systemctl' ? ' ' . $aSys : ' ' . $aShut);
            }
        }
        return $c;
    }

    public static function escapaComando($cmdline): string
    {
        $e = array();
        foreach (explode(' ', $cmdline) as $p)
            if ($p !== '')
                $e[] = escapeshellarg($p);
        return implode(' ', $e);
    }

    public static function scegliComandoAlimentazione($mode): string
    {
        foreach (self::candidatiAlimentazione($mode) as $cand)
        {
            $out = array();
            $rc = 1;
            @exec('sudo -n -l -- ' . self::escapaComando($cand) . ' >/dev/null 2>&1', $out, $rc);
            if ($rc !== 0)
                @exec('sudo -n ' . self::escapaComando(strtok($cand, " ")) . ' --help >/dev/null 2>&1', $out, $rc);
            if ($rc === 0)
                return $cand;
        }
        return '';
    }

    public static function markerPowerFile(): string
    {
        return \Salsiccia\Support\Storage::path('power_attempt.json');
    }

    public static function uptimeMacchina(): mixed
    {
        $u = @file_get_contents('/proc/uptime');
        if ($u === false)
            return null;
        $p = (float)strtok(trim((string)$u), " ");
        return $p > 0 ? $p : null;
    }

    public static function esitoTentativoPower($markerTime, $now, $uptime): string
    {
        $soglia = 180;
        $eta = $now - $markerTime;
        if ($eta < 0)
            return 'attesa';
        if ($uptime !== null && $uptime < $eta)
            return 'riuscito';
        if ($eta > $soglia)
            return 'fallito';
        return 'attesa';
    }

    public static function scriviMarkerPower($mode): void
    {
        @file_put_contents(self::markerPowerFile(), json_encode(array('t' => time(), 'mode' => $mode)));
    }

    public static function svuotaCodaStampaPreSpegnimento(): void
    {
        // F6.3 #111: valori ex set.inc da CassaConfig (stessi valori, mai define()).
        $cfg = \Salsiccia\Config\CassaConfig::carica();
        $printerConn = isset($cfg['PRINTER_CONNECTION']) ? (string)$cfg['PRINTER_CONNECTION'] : '';
        $printerNome = isset($cfg['PRINTER_NAME']) ? (string)$cfg['PRINTER_NAME'] : '';
        if ($printerConn !== 'DIRETTA')
            return;
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $printerNome))
        {
            \Salsiccia\Support\Env::log('error', 'power: cancel bloccato, PRINTER_NAME non whitelistato');
            return;
        }
        $cmd = 'cancel -a ' . escapeshellarg(\Salsiccia\Printer\CupsState::cupsQueue($printerNome)) . ' >/dev/null 2>&1';
        system($cmd, $rc);
        if ($rc !== 0)
            \Salsiccia\Support\Env::log('error', 'power: cancel coda fallito rc=' . $rc . ' printer=' . $printerNome);
    }

    public static function schedulaAzioneAlimentazione($mode): bool
    {
        $cmdline = self::scegliComandoAlimentazione($mode);
        if ($cmdline === '')
        {
            \Salsiccia\Support\Env::log('error', 'power: schedulazione ' . $mode . ' impossibile, nessun comando permesso');
            return false;
        }
        self::svuotaCodaStampaPreSpegnimento();
        $cmd = '(sleep 2; sudo ' . self::escapaComando($cmdline) . ') >/dev/null 2>&1 &';
        system($cmd, $rc);
        if ($rc !== 0)
        {
            \Salsiccia\Support\Env::log('error', 'power: schedulazione ' . $mode . ' fallita rc=' . $rc);
            return false;
        }
        self::scriviMarkerPower($mode);
        return true;
    }
}
