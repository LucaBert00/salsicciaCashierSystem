<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Support\Db;

// F4.1 #98: doppietti minimi per Db::select/exec (prepare/bind/execute/
// get_result/close), stessa forma dei doppi db_select di CatalogRepoBarcodeTest.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi Db in un solo file di test
final class SupportDbFakeResult
{
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function fetch_array(): array|false
    {
        return array('id_ordine' => 5);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi Db in un solo file di test
final class SupportDbFakeStmt
{
    public string $tipi = '';
    public array $params = array();
    public bool $closed = false;

    private bool $ok;

    public function __construct(bool $ok)
    {
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
    public function get_result(): SupportDbFakeResult
    {
        return new SupportDbFakeResult();
    }

    public function close(): void
    {
        $this->closed = true;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi Db in un solo file di test
final class SupportDbFakeMysqli
{
    public string $sql = '';
    public SupportDbFakeStmt $stmt;

    private bool $preparable;

    public function __construct(bool $ok, bool $preparable = true)
    {
        $this->stmt = new SupportDbFakeStmt($ok);
        $this->preparable = $preparable;
    }

    public function prepare(string $sql): object|false
    {
        $this->sql = $sql;
        return $this->preparable ? $this->stmt : false;
    }
}

// F4.1 #98: la casa stabile Db::select/exec esegue gli stessi prepared con "?"
// dei globali (T09), senza DB.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi Db in un solo file di test
final class SupportDbTest extends TestCase
{
    public function testSelectRitornaResultConPlaceholder(): void
    {
        $db = new SupportDbFakeMysqli(true);

        $res = Db::select($db, 'SELECT * FROM ordini WHERE id_cassa = ?', 'i', array(7));

        $this->assertInstanceOf(SupportDbFakeResult::class, $res);
        $this->assertSame('SELECT * FROM ordini WHERE id_cassa = ?', $db->sql);
        $this->assertSame('i', $db->stmt->tipi);
        $this->assertSame(array(7), $db->stmt->params);
    }

    public function testSelectMaiInterpolazione(): void
    {
        $ostile = "7' OR '1'='1";
        $db = new SupportDbFakeMysqli(true);

        Db::select($db, 'SELECT * FROM ordini WHERE id_cassa = ?', 'i', array($ostile));

        $this->assertStringNotContainsString($ostile, $db->sql);
        $this->assertStringContainsString('?', $db->sql);
        $this->assertSame(array($ostile), $db->stmt->params);
    }

    public function testSelectRitornaFalseQuandoPrepareFallisce(): void
    {
        $db = new SupportDbFakeMysqli(true, false);

        $this->assertFalse(Db::select($db, 'SELECT 1', '', array()));
    }

    public function testSelectRitornaFalseQuandoExecuteFallisce(): void
    {
        $db = new SupportDbFakeMysqli(false);

        $this->assertFalse(Db::select($db, 'SELECT 1', '', array()));
        $this->assertTrue($db->stmt->closed);
    }

    public function testExecRitornaBoolConParametriAttesi(): void
    {
        $db = new SupportDbFakeMysqli(true);

        $this->assertTrue(Db::exec($db, 'UPDATE ordini SET chiuso = ? WHERE id_ordine = ?', 'ii', array(0, 5)));
        $this->assertSame('ii', $db->stmt->tipi);
        $this->assertSame(array(0, 5), $db->stmt->params);
    }

    public function testExecRitornaFalseQuandoPrepareFallisce(): void
    {
        $db = new SupportDbFakeMysqli(true, false);

        $this->assertFalse(Db::exec($db, 'UPDATE ordini SET chiuso = 0', '', array()));
    }
}
