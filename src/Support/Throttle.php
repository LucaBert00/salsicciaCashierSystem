<?php

declare(strict_types=1);

namespace Salsiccia\Support;

// Throttle login server-side su file in storage/, fuori docroot (F5.1 #103,
// docs/ARCHITETTURA_REVISTA.md §10 punto 19 + §6 81-133 + §9 target).
// Corpi verbatim da env.inc:81-133 cosi che il blocco sopravvive al reset
// della sessione (il contatore in $_SESSION da solo si azzera cancellando il
// cookie). Scope separati: 'reserved' (backoffice) e 'admin' (cassa); stessa
// regola ovunque: 5 fail -> blocco 60s. Stessi file/scope/IP (sha1), stesse
// soglie, stessa pulizia a blocco scaduto. Metodi statici: nessun stato,
// autoload PSR-4 senza toccare composer.json. File risolti via Storage::path()
// (stesso storage/ di env.inc). Transitorio onesto: env.inc resta dov'e con
// le funzioni originali ancora presenti (delete solo in F5.5); nessun consumer
// migrato qui (login backoffice/cassa, SessionThrottleTest invariati).
final class Throttle
{
    public static function file(string $scope, string $ip = ''): string
    {
        if ($ip === '')
            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'cli');
        if (!preg_match('/^[A-Za-z0-9_-]{1,32}$/', $scope))
            $scope = 'login';
        return Storage::path('login_throttle_' . $scope . '_' . sha1($ip) . '.json');
    }

    public static function leggi(string $file): array
    {
        if (!is_readable($file))
            return array('fail' => 0, 'block_until' => 0);
        $j = json_decode((string)file_get_contents($file), true);
        if (!is_array($j))
            return array('fail' => 0, 'block_until' => 0);
        return array('fail' => max(0, (int)($j['fail'] ?? 0)), 'block_until' => max(0, (int)($j['block_until'] ?? 0)));
    }

    public static function throttled(string $scope, string $ip = '', string $file = ''): bool
    {
        $f = $file !== '' ? $file : self::file($scope, $ip);
        $st = self::leggi($f);
        if ($st['block_until'] > 0 && $st['block_until'] <= time())
        {
            @unlink($f); // blocco scaduto: si riparte puliti come il ramo sessione
            return false;
        }
        return $st['block_until'] > time();
    }

    public static function fail(string $scope, string $ip = '', string $file = ''): void
    {
        $f = $file !== '' ? $file : self::file($scope, $ip);
        $st = self::leggi($f);
        if ($st['block_until'] > time())
            return; // sotto blocco: niente estensioni, come il ramo sessione
        $st['fail']++;
        if ($st['fail'] >= 5)
        {
            $st['fail'] = 0;
            $st['block_until'] = time() + 60;
        }
        $json = json_encode($st);
        if (is_string($json))
            @file_put_contents($f, $json, LOCK_EX);
    }

    public static function ok(string $scope, string $ip = '', string $file = ''): void
    {
        $f = $file !== '' ? $file : self::file($scope, $ip);
        @unlink($f);
    }
}
