<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Stats\StatsData;

// MAINT-02 #128: lettura standby in StatsData, testata senza stub e senza
// eval con doppi prepare/bind/execute/get_result. Il fetch procedurale in
// Salsiccia\Stats e coperto da StatsMysqliOverrides, come per i contatori.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi standby in un solo file di test
final class StandbyFakeResult
{
    public array $righe;

    private int $pos = 0;

    public function __construct(array $righe)
    {
        $this->righe = $righe;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli_result, doppio onesto
    public function fetch_array(): array|null
    {
        if ($this->pos >= count($this->righe)) {
            return null;
        }

        return $this->righe[$this->pos++];
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi standby in un solo file di test
final class StandbyFakeStmt
{
    public string $sql = '';

    public string $tipi = '';

    public array $params = array();

    private StandbyFakeResult|null $result;

    private bool $ok;

    public function __construct(StandbyFakeResult|null $result, bool $ok = true)
    {
        $this->result = $result;
        $this->ok = $ok;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function bind_param(string $tipi, mixed ...$params): bool
    {
        $this->tipi = $tipi;
        $this->params = $params;
        return true;
    }

    public function execute(): bool
    {
        return $this->ok;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function get_result(): StandbyFakeResult|false
    {
        return $this->result ?? false;
    }

    public function close(): void
    {
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi standby in un solo file di test
final class StandbyFakeMysqli
{
    public string $sql = '';

    public StandbyFakeStmt $stmt;

    private bool $preparable;

    public function __construct(StandbyFakeResult|null $result, bool $preparable = true, bool $ok = true)
    {
        $this->stmt = new StandbyFakeStmt($result, $ok);
        $this->preparable = $preparable;
    }

    public function prepare(string $sql): object|false
    {
        $this->sql = $sql;
        $this->stmt->sql = $sql;
        return $this->preparable ? $this->stmt : false;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- test della lettura standby
final class StatsDataStandbyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/StatsMysqliOverrides.php';
    }

    public function testOrdiniStandbyRitornaRighe(): void
    {
        $righe = array(
            array('id_ordine' => 20, 'data_ora' => '2026-10-01 10:00:00', 'totale' => 5.0),
        );
        $db = new StandbyFakeMysqli(new StandbyFakeResult($righe));

        $this->assertSame($righe, StatsData::ordiniStandby($db));
        $this->assertStringContainsString("chiuso = 'S'", $db->sql);
        $this->assertStringContainsString('FROM ordini', $db->sql);
        $this->assertSame('', $db->stmt->tipi);
        $this->assertSame(array(), $db->stmt->params);
    }

    public function testOrdiniStandbyVuotoRitornaListaVuota(): void
    {
        $db = new StandbyFakeMysqli(new StandbyFakeResult(array()));

        $this->assertSame(array(), StatsData::ordiniStandby($db));
    }

    public function testOrdiniStandbyDbKoRitornaListaVuotaMaiFatal(): void
    {
        $db = new StandbyFakeMysqli(null, false);

        $this->assertSame(array(), StatsData::ordiniStandby($db));
    }
}
