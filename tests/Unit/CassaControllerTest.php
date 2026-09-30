<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\CassaController;

// F1.2 #84: CassaController::gestisci() risolve la action via routes/cassa.php
// (unica fonte di verita F1.1), autorizza via campo auth, esegue gli handler
// esistenti e restituisce la view validata. Niente DB, niente rete: gli
// handler legacy non sono caricati nel processo di test, quindi il dispatch
// li salta (function_exists) e resta risoluzione + auth da verificare.
final class CassaControllerTest extends TestCase
{
    /** @var array<string,array{handler:?string,view:string,auth:?string}> */
    private array $tabella;

    private array $getBackup = [];

    private mixed $sessionBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/FakeMysqli.php';
        require_once __DIR__ . '/FakeStmt.php';
        $this->tabella = (array) require __DIR__ . '/../../routes/cassa.php';
        $this->getBackup = $_GET;
        $this->sessionBackup = $_SESSION ?? [];
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_GET = $this->getBackup;
        $_SESSION = is_array($this->sessionBackup) ? $this->sessionBackup : [];
        unset($GLOBALS['cassa_azione_annulla_args']);
        parent::tearDown();
    }

    /** @return array<string,array{string}> */
    public static function azioniProvider(): array
    {
        $tabella = (array) require __DIR__ . '/../../routes/cassa.php';
        $casi = [];
        foreach (array_keys($tabella) as $action) {
            $casi[(string) $action] = [(string) $action];
        }
        return $casi;
    }

    /** @return array<string,array{string}> */
    public static function azioniAdminProvider(): array
    {
        $tabella = (array) require __DIR__ . '/../../routes/cassa.php';
        $casi = [];
        foreach ($tabella as $action => $riga) {
            if (($riga['auth'] ?? null) === 'admin') {
                $casi[(string) $action] = [(string) $action];
            }
        }
        return $casi;
    }

    public function testTabellaHaSetteAdmin(): void
    {
        $this->assertCount(7, self::azioniAdminProvider());
    }

    #[DataProvider('azioniProvider')]
    public function testOgniActionConAdminRitornaViewDiTabella(string $action): void
    {
        $_SESSION = ['admin' => true];
        $_GET = ['action' => $action];

        $vista = CassaController::gestisci(new FakeMysqli(true), 7);

        $this->assertSame($this->tabella[$action]['view'], $vista, 'action=' . $action);
    }

    #[DataProvider('azioniAdminProvider')]
    public function testAdminSenzaLoginChiedeLogin(string $action): void
    {
        $_SESSION = [];
        $_GET = ['action' => $action];

        $vista = CassaController::gestisci(new FakeMysqli(true), 7);

        // Stessa schermata del redirect odierno index.php?action=c (login).
        $this->assertSame($this->tabella['c']['view'], $vista, 'action=' . $action);
    }

    public function testActionIgnotaRitornaErroreKioskMaiFatal(): void
    {
        $_SESSION = ['admin' => true];
        $_GET = ['action' => 'azione-che-non-esiste'];

        $this->assertSame('azione-non-valida', CassaController::gestisci(new FakeMysqli(true), 7));
    }

    public function testActionMancanteRitornaCassa(): void
    {
        $_SESSION = [];
        $_GET = [];

        $this->assertSame('cassa', CassaController::gestisci(new FakeMysqli(true), 7));
    }

    public function testActionNonStringaRitornaCassaMaiFatal(): void
    {
        $_SESSION = [];
        $_GET = ['action' => ['r']];

        $this->assertSame('cassa', CassaController::gestisci(new FakeMysqli(true), 7));
    }

    public function testDispatchEsegueHandlerDiTabellaConStessiArgomenti(): void
    {
        // Doppio minimale dell'handler reale (stesso nome di tabella, mai
        // caricato qui): registra gli argomenti ricevuti, mai exit.
        if (!function_exists('cassa_azione_annulla')) {
            eval('function cassa_azione_annulla($db, $idCassa): void'
                . ' { $GLOBALS["cassa_azione_annulla_args"] = array($db, $idCassa); }');
        }
        $db = new FakeMysqli(true);
        $_SESSION = ['admin' => true];
        $_GET = ['action' => 'r'];

        $vista = CassaController::gestisci($db, 7);

        $this->assertSame('cassa', $vista);
        $this->assertSame([$db, 7], $GLOBALS['cassa_azione_annulla_args'] ?? null);
    }
}
