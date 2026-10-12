<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\OrderService;

// MAINT-02 #128: letture ordine/carrello in OrderService, testate senza
// stub e senza eval con doppi prepare/bind/execute/get_result. Le
// procedurali mysqli_* in Salsiccia\Cassa sono coperte da
// CassaMysqliOverrides, come per calcolaTotali.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di lettura in un solo file di test
final class Maint02FakeResult
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

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di lettura in un solo file di test
final class Maint02FakeStmt
{
    public string $sql;

    public string $tipi = '';

    public array $params = array();

    private Maint02FakeResult|null $result;

    private bool $ok;

    public function __construct(string $sql, Maint02FakeResult|null $result, bool $ok = true)
    {
        $this->sql = $sql;
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
    public function get_result(): Maint02FakeResult|false
    {
        return $this->result ?? false;
    }

    public function close(): void
    {
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di lettura in un solo file di test
final class Maint02FakeMysqli
{
    /** @var Maint02FakeStmt[] */
    public array $stmts = array();

    /** @var array<string, array> */
    private array $code;

    public function __construct(array $code)
    {
        $this->code = $code;
    }

    public function prepare(string $sql): object|false
    {
        foreach ($this->code as $chiave => &$coda) {
            if (str_contains($sql, $chiave) && count($coda) > 0) {
                $risposta = array_shift($coda);
                if ($risposta === false) {
                    return false;
                }
                $stmt = new Maint02FakeStmt($sql, $risposta['result'] ?? null, $risposta['ok'] ?? true);
                $this->stmts[] = $stmt;
                return $stmt;
            }
        }
        $stmt = new Maint02FakeStmt($sql, new Maint02FakeResult(array()));
        $this->stmts[] = $stmt;
        return $stmt;
    }

    public function stmtPer(string $frammento): Maint02FakeStmt|null
    {
        foreach (array_reverse($this->stmts) as $stmt) {
            if (str_contains($stmt->sql, $frammento)) {
                return $stmt;
            }
        }

        return null;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- test delle 4 letture ordine/carrello
final class OrderServiceMaint02Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/CassaMysqliOverrides.php';
    }

    public function testTipoOrdineApertoRitornaTipoConScopeCassa(): void
    {
        $db = new Maint02FakeMysqli(array(
            'FROM ordini' => array(array('result' => new Maint02FakeResult(array(array('tipo' => 'pre'))))),
        ));

        $this->assertSame('pre', (new OrderService($db, 7))->tipoOrdineAperto());

        $stmt = $db->stmtPer('FROM ordini');
        $this->assertNotNull($stmt);
        $this->assertStringContainsString('id_cassa', $stmt->sql);
        $this->assertStringContainsString("chiuso = '0'", $stmt->sql);
        $this->assertSame('i', $stmt->tipi);
        $this->assertSame(array(7), $stmt->params);
    }

    public function testTipoOrdineApertoVuotoRitornaNull(): void
    {
        $db = new Maint02FakeMysqli(array(
            'FROM ordini' => array(array('result' => new Maint02FakeResult(array()))),
        ));

        $this->assertNull((new OrderService($db, 7))->tipoOrdineAperto());
    }

    public function testTipoOrdineApertoDbKoRitornaNullMaiFatal(): void
    {
        $db = new Maint02FakeMysqli(array('FROM ordini' => array(false)));

        $this->assertNull((new OrderService($db, 7))->tipoOrdineAperto());
    }

    public function testRigheModificaOrdineRitornaRigheConScopeCassa(): void
    {
        $righe = array(
            array('id' => 1, 'descrizione_prod' => 'PANINO', 'quantita' => 2, 'totale' => 7.0, 'tot_ord' => 9.0, 'id_ord' => 10),
            array('id' => 2, 'descrizione_prod' => 'BIRRA', 'quantita' => 1, 'totale' => 2.0, 'tot_ord' => 9.0, 'id_ord' => 10),
        );
        $db = new Maint02FakeMysqli(array(
            'righe_ordini' => array(array('result' => new Maint02FakeResult($righe))),
        ));

        $this->assertSame($righe, (new OrderService($db, 7))->righeModificaOrdine());

        $stmt = $db->stmtPer('righe_ordini');
        $this->assertNotNull($stmt);
        $this->assertStringContainsString('id_cassa', $stmt->sql);
        $this->assertSame('i', $stmt->tipi);
        $this->assertSame(array(7), $stmt->params);
    }

    public function testRigheModificaOrdineVuotoRitornaListaVuota(): void
    {
        $db = new Maint02FakeMysqli(array(
            'righe_ordini' => array(array('result' => new Maint02FakeResult(array()))),
        ));

        $this->assertSame(array(), (new OrderService($db, 7))->righeModificaOrdine());
    }

    public function testRiepilogoOrdineApertoRitornaIdETotaleCastati(): void
    {
        $db = new Maint02FakeMysqli(array(
            'FROM ordini' => array(array('result' => new Maint02FakeResult(array(array('id_ordine' => '10', 'totale' => '9.00'))))),
        ));

        $this->assertSame(array('id_ordine' => 10, 'totale' => 9.0), (new OrderService($db, 7))->riepilogoOrdineAperto());

        $stmt = $db->stmtPer('FROM ordini');
        $this->assertNotNull($stmt);
        $this->assertStringContainsString('id_cassa', $stmt->sql);
        $this->assertSame('i', $stmt->tipi);
        $this->assertSame(array(7), $stmt->params);
    }

    public function testRiepilogoOrdineApertoVuotoRitornaNull(): void
    {
        $db = new Maint02FakeMysqli(array(
            'FROM ordini' => array(array('result' => new Maint02FakeResult(array()))),
        ));

        $this->assertNull((new OrderService($db, 7))->riepilogoOrdineAperto());
    }

    public function testRigheListaProdottiRitornaRigheConScopeCassa(): void
    {
        $righe = array(
            array('descrizione_prod' => 'PANINO', 'quantita' => 2, 'totale' => 7.0),
            array('descrizione_prod' => 'BIRRA', 'quantita' => 1, 'totale' => 2.0),
        );
        $db = new Maint02FakeMysqli(array(
            'righe_ordini' => array(array('result' => new Maint02FakeResult($righe))),
        ));

        $this->assertSame($righe, (new OrderService($db, 7))->righeListaProdotti());

        $stmt = $db->stmtPer('righe_ordini');
        $this->assertNotNull($stmt);
        $this->assertStringContainsString('id_cassa', $stmt->sql);
        $this->assertSame('i', $stmt->tipi);
        $this->assertSame(array(7), $stmt->params);
    }

    public function testRigheListaProdottiVuotoRitornaListaVuota(): void
    {
        $db = new Maint02FakeMysqli(array(
            'righe_ordini' => array(array('result' => new Maint02FakeResult(array()))),
        ));

        $this->assertSame(array(), (new OrderService($db, 7))->righeListaProdotti());
    }
}
