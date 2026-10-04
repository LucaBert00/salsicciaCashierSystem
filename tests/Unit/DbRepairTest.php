<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\System\DbRepair;

// F2.2 #89: doppi minimi per query()/fetch_array, gli esistenti
// FakeMysqli/FakeStmt coprono solo prepare (T30) — stessa forma del
// precedente CatalogRepoBarcodeTest (doppi locali in un solo file).
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi query in un solo file di test
final class RepairFakeResult
{
    private array $righe;
    private int $pos = 0;

    public function __construct(array $tabelle)
    {
        $this->righe = $tabelle;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function fetch_array(): array|false
    {
        if ($this->pos >= count($this->righe)) {
            return false;
        }
        $tabella = $this->righe[$this->pos];
        $this->pos++;
        return array($tabella);
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi query in un solo file di test
final class RepairFakeMysqli
{
    public string $error = '';
    /** @var list<string> */
    public array $queries = array();

    private array $tabelle;
    /** @var array<string, string> tabella => errore */
    private array $fallite;

    public function __construct(array $tabelle, array $fallite = array())
    {
        $this->tabelle = $tabelle;
        $this->fallite = $fallite;
    }

    public function query(string $sql): RepairFakeResult|bool
    {
        $this->queries[] = $sql;
        if ($sql === 'SHOW TABLES') {
            return new RepairFakeResult($this->tabelle);
        }
        foreach ($this->fallite as $tabella => $errore) {
            if ($sql === 'REPAIR TABLE `' . $tabella . '`') {
                $this->error = $errore;
                return false;
            }
        }
        return true;
    }
}

// F2.2 #89: DbRepair::run() pura — whitelist ok, non-whitelist skippata,
// errore REPAIR riportato. Mai DB reale.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi query in un solo file di test
final class DbRepairTest extends TestCase
{
    public function testTabellaWhitelistataRiparata(): void
    {
        $db = new RepairFakeMysqli(array('ordini'));

        $righe = DbRepair::run($db);

        $this->assertSame(array(array('table' => 'ordini', 'ok' => true, 'error' => '')), $righe);
        $this->assertContains('REPAIR TABLE `ordini`', $db->queries);
    }

    public function testTabellaNonWhitelistataSkippata(): void
    {
        $db = new RepairFakeMysqli(array('ordini', 'evil-table;DROP', 'x`y'));

        $righe = DbRepair::run($db);

        $this->assertSame(array(array('table' => 'ordini', 'ok' => true, 'error' => '')), $righe);
        foreach ($db->queries as $sql) {
            $this->assertStringNotContainsString('evil-table;DROP', $sql);
            $this->assertStringNotContainsString('x`y', $sql);
        }
    }

    public function testErroreRepairRiportato(): void
    {
        $db = new RepairFakeMysqli(array('ordini'), array('ordini' => 'gone'));

        $righe = DbRepair::run($db);

        $this->assertSame(array(array('table' => 'ordini', 'ok' => false, 'error' => 'gone')), $righe);
    }
}
