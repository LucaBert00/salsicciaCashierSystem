<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Stats\StatsData;

// F4.2 #99: contatori_totali() con doppi (stessa superficie dei doppi Db di
// SupportDbTest: prepare/bind/execute/get_result/close). Il fetch procedurale
// dentro StatsData (namespace Salsiccia\Stats) e' coperto da
// tests/Unit/StatsMysqliOverrides.php. Mai DB reale.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi contatori in un solo file di test
final class ContatoriFakeResult
{
    public array $righe;
    private int $pos = 0;

    public function __construct(array $righe)
    {
        $this->righe = $righe;
    }

    // OO, chiamata solo dall'override Salsiccia\Stats\mysqli_fetch_array.
    public function fetch_array(): array|null
    {
        if ($this->pos >= count($this->righe)) {
            return null;
        }
        return $this->righe[$this->pos++];
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi contatori in un solo file di test
final class ContatoriFakeStmt
{
    public string $tipi = '';
    public array $params = array();
    public bool $closed = false;

    private ContatoriFakeResult|null $result;
    private bool $ok;

    public function __construct(ContatoriFakeResult|null $result, bool $ok = true)
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
    public function get_result(): ContatoriFakeResult|false
    {
        return $this->result ?? false;
    }

    public function close(): void
    {
        $this->closed = true;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi contatori in un solo file di test
final class ContatoriFakeMysqli
{
    public string $sql = '';
    public int $prepares = 0;
    public ContatoriFakeStmt $stmt;

    public function __construct(ContatoriFakeResult|null $result, bool $preparable = true, bool $ok = true)
    {
        $this->stmt = new ContatoriFakeStmt($result, $ok);
        $this->preparable = $preparable;
    }

    private bool $preparable;

    public function prepare(string $sql): object|false
    {
        $this->sql = $sql;
        $this->prepares++;
        return $this->preparable ? $this->stmt : false;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi contatori in un solo file di test
final class StatsDataContatoriTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/StatsMysqliOverrides.php';
    }

    public function testRitornaListaCastataInOrdineDiNome(): void
    {
        $db = new ContatoriFakeMysqli(new ContatoriFakeResult(array(
            array('id_contatore' => '2', 'nome' => 'BIRRA', 'totale' => '10.5'),
            array('id_contatore' => '1', 'nome' => 'ACQUA', 'totale' => '0'),
        )));

        $this->assertSame(
            array(
                array('id_contatore' => 2, 'nome' => 'BIRRA', 'totale' => 10.5),
                array('id_contatore' => 1, 'nome' => 'ACQUA', 'totale' => 0.0),
            ),
            StatsData::contatori_totali($db)
        );
    }

    public function testUnicoStatementGroupBy(): void
    {
        $db = new ContatoriFakeMysqli(new ContatoriFakeResult(array()));

        StatsData::contatori_totali($db);

        $this->assertSame(1, $db->prepares);
        $this->assertStringContainsString('GROUP BY', $db->sql);
        $this->assertStringContainsString('ORDER BY', $db->sql);
        $this->assertSame('', $db->stmt->tipi);
    }

    public function testVuotoRitornaListaVuota(): void
    {
        $db = new ContatoriFakeMysqli(new ContatoriFakeResult(array()));

        $this->assertSame(array(), StatsData::contatori_totali($db));
    }

    public function testDbKoRitornaListaVuotaMaiFatal(): void
    {
        $db = new ContatoriFakeMysqli(null, false);

        $this->assertSame(array(), StatsData::contatori_totali($db));
    }
}
