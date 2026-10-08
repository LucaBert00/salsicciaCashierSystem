<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\OrderService;

// T30: l'unita' si costruisce con un manico DB finto, mai con il globale.
// Il percorso riattiva() passa solo da db_exec(), quindi gira senza DB
// (niente mysqli_* procedurali da imitare).
// F4.2 #99: doppi scriptabili per calcolaTotali (stessa superficie dei doppi
// Db di SupportDbTest: prepare/bind/execute/get_result/close). Le procedurali
// mysqli_* dentro OrderService (namespace Salsiccia\Cassa) sono coperte da
// tests/Unit/CassaMysqliOverrides.php, che delega al globale per
// mysqli_result veri.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi calcolaTotali in un solo file di test
final class CalcolaTotaliFakeResult
{
    public array $righe;
    private int $pos = 0;

    public function __construct(array $righe)
    {
        $this->righe = $righe;
    }

    // OO, chiamata solo dall'override Salsiccia\Cassa\mysqli_fetch_array.
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli_result, doppio onesto
    public function fetch_array(): array|null
    {
        if ($this->pos >= count($this->righe)) {
            return null;
        }
        return $this->righe[$this->pos++];
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi calcolaTotali in un solo file di test
final class CalcolaTotaliFakeStmt
{
    public string $sql;
    public string $tipi = '';
    public array $params = array();
    public bool $closed = false;

    private CalcolaTotaliFakeResult|null $result;
    private bool $ok;

    public function __construct(string $sql, CalcolaTotaliFakeResult|null $result, bool $ok = true)
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
    public function get_result(): CalcolaTotaliFakeResult|false
    {
        return $this->result ?? false;
    }

    public function close(): void
    {
        $this->closed = true;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi calcolaTotali in un solo file di test
final class CalcolaTotaliFakeMysqli
{
    /** @var CalcolaTotaliFakeStmt[] */
    public array $stmts = array();
    public int $begin = 0;
    public int $commit = 0;
    public int $rollback = 0;

    /** @var array<string, array> risposte per sottostringa SQL (false = prepare fallita) */
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
                $stmt = new CalcolaTotaliFakeStmt($sql, $risposta['result'] ?? null, $risposta['ok'] ?? true);
                $this->stmts[] = $stmt;
                return $stmt;
            }
        }
        $stmt = new CalcolaTotaliFakeStmt($sql, new CalcolaTotaliFakeResult(array()));
        $this->stmts[] = $stmt;
        return $stmt;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli, doppio onesto
    public function begin_transaction(): bool
    {
        $this->begin++;
        return true;
    }

    public function commit(): bool
    {
        $this->commit++;
        return true;
    }

    public function rollback(): bool
    {
        $this->rollback++;
        return true;
    }

    public function stmtPer(string $frammento): CalcolaTotaliFakeStmt|null
    {
        foreach (array_reverse($this->stmts) as $stmt) {
            if (str_contains($stmt->sql, $frammento)) {
                return $stmt;
            }
        }
        return null;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi calcolaTotali in un solo file di test
final class OrderServiceFakeDbTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/FakeMysqli.php';
        require_once __DIR__ . '/FakeStmt.php';
        require_once __DIR__ . '/CassaMysqliOverrides.php';
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

    // F4.2 #99: percorso base — riga valorizzata, totali attesi, commit proprio.
    public function testCalcolaTotaliAggiornaRigaEOrdineConCommit(): void
    {
        $db = new CalcolaTotaliFakeMysqli(array(
            'FOR UPDATE' => array(array('result' => new CalcolaTotaliFakeResult(array(array('id_ordine' => 9))))),
            'quantita, prezzo' => array(array('result' => new CalcolaTotaliFakeResult(array(array('quantita' => 2, 'prezzo' => 3.5))))),
            'UPDATE righe_ordini set' => array(array('ok' => true)),
            'sum(quantita)' => array(array('result' => new CalcolaTotaliFakeResult(array(array('n_pezzi' => 5, 'totale' => 17.5))))),
            'UPDATE ordini set' => array(array('ok' => true)),
        ));

        $this->assertTrue((new OrderService($db, 7))->calcolaTotali(4, 9));
        $this->assertSame(1, $db->begin);
        $this->assertSame(1, $db->commit);
        $this->assertSame(0, $db->rollback);

        $righe = $db->stmtPer('UPDATE righe_ordini set');
        $this->assertNotNull($righe);
        $this->assertSame('idii', $righe->tipi);
        $this->assertSame(array(2, 7.0, 9, 4), $righe->params);

        $ordini = $db->stmtPer('UPDATE ordini set n_pezzi = ?, totale = ?');
        $this->assertNotNull($ordini);
        $this->assertSame('idi', $ordini->tipi);
        $this->assertSame(array(5, 17.5, 9), $ordini->params);
    }

    // F4.2 #99: ordine svuotato — nessuna riga, azzera n_pezzi/totale, mai fatal.
    // I 2 warning "offset on null" sono il percorso verbatim (GROUP BY vuoto =
    // fetch null), invariato dalla migrazione.
    public function testCalcolaTotaliOrdineVuotoAzzera(): void
    {
        $db = new CalcolaTotaliFakeMysqli(array(
            'FOR UPDATE' => array(array('result' => new CalcolaTotaliFakeResult(array(array('id_ordine' => 9))))),
            'quantita, prezzo' => array(array('result' => new CalcolaTotaliFakeResult(array()))),
            'sum(quantita)' => array(array('result' => new CalcolaTotaliFakeResult(array()))),
            'UPDATE ordini set' => array(array('ok' => true)),
        ));

        $this->assertTrue((new OrderService($db, 7))->calcolaTotali(4, 9));
        $this->assertNull($db->stmtPer('UPDATE righe_ordini set'));

        $azzera = $db->stmtPer('UPDATE ordini set n_pezzi = 0, totale = 0');
        $this->assertNotNull($azzera);
        $this->assertSame('i', $azzera->tipi);
        $this->assertSame(array(9), $azzera->params);
    }

    // F4.2 #99 (T07): con $in_txn la transazione resta al chiamante mq/mr.
    public function testCalcolaTotaliInTxnNonApreNeChiudeTransazione(): void
    {
        $db = new CalcolaTotaliFakeMysqli(array(
            'FOR UPDATE' => array(array('result' => new CalcolaTotaliFakeResult(array(array('id_ordine' => 9))))),
            'quantita, prezzo' => array(array('result' => new CalcolaTotaliFakeResult(array(array('quantita' => 1, 'prezzo' => 2.0))))),
            'UPDATE righe_ordini set' => array(array('ok' => true)),
            'sum(quantita)' => array(array('result' => new CalcolaTotaliFakeResult(array(array('n_pezzi' => 1, 'totale' => 2.0))))),
            'UPDATE ordini set' => array(array('ok' => true)),
        ));

        $this->assertTrue((new OrderService($db, 7))->calcolaTotali(4, 9, true));
        $this->assertSame(0, $db->begin);
        $this->assertSame(0, $db->commit);
        $this->assertSame(0, $db->rollback);
    }

    // F4.2 #99: update fallito — rollback proprio + warning, mai commit.
    public function testCalcolaTotaliRollbackQuandoUpdateRigheFallisce(): void
    {
        $db = new CalcolaTotaliFakeMysqli(array(
            'FOR UPDATE' => array(array('result' => new CalcolaTotaliFakeResult(array(array('id_ordine' => 9))))),
            'quantita, prezzo' => array(array('result' => new CalcolaTotaliFakeResult(array(array('quantita' => 2, 'prezzo' => 3.5))))),
            'UPDATE righe_ordini set' => array(array('ok' => false)),
        ));

        $this->assertFalse((new OrderService($db, 7))->calcolaTotali(4, 9));
        $this->assertSame(1, $db->begin);
        $this->assertSame(0, $db->commit);
        $this->assertSame(1, $db->rollback);
    }

    // F4.2 #99: lock FOR UPDATE non supportato (MyISAM) — ripiega sulla SELECT semplice.
    public function testCalcolaTotaliRipiegaSenzaForUpdate(): void
    {
        $db = new CalcolaTotaliFakeMysqli(array(
            'FOR UPDATE' => array(false),
            'FROM ordini WHERE id_ordine' => array(array('result' => new CalcolaTotaliFakeResult(array(array('id_ordine' => 9))))),
            'quantita, prezzo' => array(array('result' => new CalcolaTotaliFakeResult(array(array('quantita' => 1, 'prezzo' => 2.0))))),
            'UPDATE righe_ordini set' => array(array('ok' => true)),
            'sum(quantita)' => array(array('result' => new CalcolaTotaliFakeResult(array(array('n_pezzi' => 1, 'totale' => 2.0))))),
            'UPDATE ordini set' => array(array('ok' => true)),
        ));

        $this->assertTrue((new OrderService($db, 7))->calcolaTotali(4, 9));
        $this->assertSame(1, $db->commit);
        $this->assertNotNull($db->stmtPer('SELECT id_ordine FROM ordini WHERE id_ordine = ?'));
    }
}
