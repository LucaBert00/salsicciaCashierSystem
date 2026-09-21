<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\OrderService;

// T30: l'unita' si costruisce con un manico DB finto, mai con il globale.
// Il percorso riattiva() passa solo da db_exec(), quindi gira senza DB
// (niente mysqli_* procedurali da imitare).
final class OrderServiceFakeDbTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/FakeMysqli.php';
        require_once __DIR__ . '/FakeStmt.php';
    }

    public function testRiattivaStessaCassaConParametriAttesi(): void
    {
        $db = new FakeMysqli(true);
        $servizio = new OrderService($db, 7);

        $this->assertTrue($servizio->riattiva(5));
        $this->assertSame(
            "UPDATE ordini SET chiuso = '0' WHERE id_ordine = ? AND id_cassa = ? AND chiuso = 'S'",
            $db->sql
        );
        $this->assertSame('ii', $db->stmt->tipi);
        $this->assertSame(array(5, 7), $db->stmt->params);
    }

    public function testRiattivaRitornaFalseQuandoPrepareFallisce(): void
    {
        $db = new FakeMysqli(false);

        $this->assertFalse((new OrderService($db, 7))->riattiva(5));
    }
}
