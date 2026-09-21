<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Catalog\CatalogRepo;

// Issue #46: doppietti minimi per db_select (prepare/bind/execute/get_result),
// gli esistenti FakeMysqli/FakeStmt coprono solo db_exec (T30) — stessa forma.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi db_select in un solo file di test
final class BarcodeFakeResult
{
    private $row;
    private bool $fetched = false;

    public function __construct($row)
    {
        $this->row = $row;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function fetch_array(): array|false
    {
        if ($this->fetched) {
            return false;
        }
        $this->fetched = true;
        return $this->row ?? false;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi db_select in un solo file di test
final class BarcodeFakeStmt
{
    public string $tipi = '';
    public array $params = array();
    private $row;

    public function __construct($row)
    {
        $this->row = $row;
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
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function get_result(): BarcodeFakeResult
    {
        return new BarcodeFakeResult($this->row);
    }

    public function close(): void
    {
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi db_select in un solo file di test
final class BarcodeFakeMysqli
{
    public string $sql = '';
    public bool $prepareCalled = false;
    public BarcodeFakeStmt $stmt;

    public function __construct($row)
    {
        $this->stmt = new BarcodeFakeStmt($row);
    }

    public function prepare(string $sql): object|false
    {
        $this->sql = $sql;
        $this->prepareCalled = true;
        return $this->stmt;
    }
}

// Issue #46: trovaIdPerBarcode — prepared, trim/cap, mai interpolazione.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi db_select in un solo file di test
final class CatalogRepoBarcodeTest extends TestCase
{
    public function testTrovaIdPerBarcodeRitornaIdQuandoTrovato(): void
    {
        $db = new BarcodeFakeMysqli(array('id_prodotto' => 42));

        $this->assertSame(42, (new CatalogRepo($db))->trovaIdPerBarcode('ABC123'));
        $this->assertStringContainsString('barcode = ?', $db->sql);
        $this->assertSame('s', $db->stmt->tipi);
        $this->assertSame(array('ABC123'), $db->stmt->params);
    }

    public function testTrovaIdPerBarcodeRitornaNullQuandoIgnoto(): void
    {
        $db = new BarcodeFakeMysqli(null);

        $this->assertNull((new CatalogRepo($db))->trovaIdPerBarcode('ZZZ999'));
    }

    public function testTrovaIdPerBarcodeTrimmaECappaA20(): void
    {
        $db = new BarcodeFakeMysqli(array('id_prodotto' => 7));
        (new CatalogRepo($db))->trovaIdPerBarcode('  ABC123  ');
        $this->assertSame(array('ABC123'), $db->stmt->params);

        $lungo = str_repeat('X', 30);
        $db2 = new BarcodeFakeMysqli(array('id_prodotto' => 7));
        (new CatalogRepo($db2))->trovaIdPerBarcode($lungo);
        $this->assertSame(array(substr($lungo, 0, 20)), $db2->stmt->params);
        $this->assertSame(20, strlen($db2->stmt->params[0]));
    }

    public function testTrovaIdPerBarcodeVuotoOTrattinoSenzaQuery(): void
    {
        $db = new BarcodeFakeMysqli(array('id_prodotto' => 1));
        $this->assertNull((new CatalogRepo($db))->trovaIdPerBarcode('   '));
        $this->assertFalse($db->prepareCalled);

        $db2 = new BarcodeFakeMysqli(array('id_prodotto' => 1));
        $this->assertNull((new CatalogRepo($db2))->trovaIdPerBarcode('-'));
        $this->assertFalse($db2->prepareCalled);
    }

    public function testTrovaIdPerBarcodeMaiInterpolazione(): void
    {
        $bc = "X' OR '1'='1";
        $db = new BarcodeFakeMysqli(null);
        (new CatalogRepo($db))->trovaIdPerBarcode($bc);
        $this->assertStringNotContainsString($bc, $db->sql);
        $this->assertStringContainsString('?', $db->sql);
    }
}
