<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\StampaController;

// REL-01: scoping id_cassa sul ramo stampato (?action=s&stampato=).
// Negativo: id_ordine di altra cassa = zero righe, zero emissione, zero
// totale cross-terminale. Positivo: stessa cassa appena stampata = 1
// emissione con totale corretto.
// Doppietti scriptabili per db_select (prepare/bind/execute/get_result) +
// query diretta di Fiscale (prepare/bind/execute/get_result/fetch_all).
// tests/Unit/FakeMysqli.php e FakeStmt.php coprono solo db_exec (T30,
// senza get_result): qui serve la stessa superficie dei doppi db_select di
// CatalogRepoBarcodeTest e dei doppi calcolaTotali di OrderServiceFakeDbTest.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi scoping stampato in un solo file di test
final class StampaScopingFakeResult
{
    /** @var array<int,array<string,mixed>> */
    public array $righe;

    private int $pos = 0;

    /**
     * @param array<int,array<string,mixed>> $righe
     */
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

    // Superficie usata da Fiscale::emettiScontrino ($res->fetch_all(...)).
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli_result, doppio onesto
    public function fetch_all(int $mode = MYSQLI_ASSOC): array
    {
        return $this->righe;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi scoping stampato in un solo file di test
final class StampaScopingFakeStmt
{
    public string $sql;

    public string $tipi = '';

    /** @var array<int,mixed> */
    public array $params = array();

    private StampaScopingFakeMysqli $db;

    public function __construct(string $sql, StampaScopingFakeMysqli $db)
    {
        $this->sql = $sql;
        $this->db = $db;
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
    public function get_result(): StampaScopingFakeResult
    {
        return $this->db->risultatoPer($this->sql, $this->tipi, $this->params);
    }

    public function close(): void
    {
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi scoping stampato in un solo file di test
final class StampaScopingFakeMysqli
{
    /** @var string[] */
    public array $queries = array();

    /** @var StampaScopingFakeStmt[] */
    public array $stmts = array();

    /** @var array<int,array<string,mixed>> ordini per id_ordine */
    private array $ordini;

    /** @var array<int,array<int,array<string,mixed>>> aggregate fiscali per id_ordine */
    private array $aggregate;

    /**
     * @param array<int,array<string,mixed>> $ordini
     * @param array<int,array<int,array<string,mixed>>> $aggregate
     */
    public function __construct(array $ordini, array $aggregate)
    {
        $this->ordini = $ordini;
        $this->aggregate = $aggregate;
    }

    public function prepare(string $sql): object|false
    {
        $this->queries[] = $sql;
        $stmt = new StampaScopingFakeStmt($sql, $this);
        $this->stmts[] = $stmt;
        return $stmt;
    }

    /**
     * @param array<int,mixed> $params
     */
    public function risultatoPer(string $sql, string $tipi, array $params): StampaScopingFakeResult
    {
        if (str_contains($sql, 'FROM ordini') && str_contains($sql, 'SELECT totale')) {
            $id = (int)($params[0] ?? 0);
            if (!isset($this->ordini[$id])) {
                return new StampaScopingFakeResult(array());
            }

            $riga = $this->ordini[$id];
            if (str_contains($sql, 'id_cassa')) {
                $cassa = (int)($params[1] ?? 0);
                if ((int)$riga['id_cassa'] !== $cassa) {
                    return new StampaScopingFakeResult(array());
                }

                if (str_contains($sql, "chiuso = '1'") && (string)$riga['chiuso'] !== '1') {
                    return new StampaScopingFakeResult(array());
                }

                return new StampaScopingFakeResult(array($riga));
            }

            // Query senza scope: il DB vero restituirebbe la riga di
            // qualunque cassa (e' il difetto REL-01). Il doppio lo replica
            // per rendere il test negativo rosso prima del fix.
            return new StampaScopingFakeResult(array($riga));
        }

        if (str_contains($sql, 'righe_ordini')) {
            $id = (int)($params[0] ?? 0);
            return new StampaScopingFakeResult($this->aggregate[$id] ?? array());
        }

        return new StampaScopingFakeResult(array());
    }

    public function stmtPer(string $frammento): StampaScopingFakeStmt|null
    {
        foreach (array_reverse($this->stmts) as $stmt) {
            if (str_contains($stmt->sql, $frammento)) {
                return $stmt;
            }
        }

        return null;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi scoping stampato in un solo file di test
final class StampaScopingTest extends TestCase
{
    private array $getBackup = [];

    private mixed $sessionBackup = null;

    private string $dir = '';

    /** @var array<string,string|false> */
    private array $envBackup = array();

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/FakeMysqli.php';
        require_once __DIR__ . '/FakeStmt.php';
        require_once __DIR__ . '/CassaMysqliOverrides.php';
        $this->getBackup = $_GET;
        $this->sessionBackup = $_SESSION ?? [];
        $_GET = [];
        $_SESSION = ['id_cassa' => 7];
        if (!function_exists('cassaCorrente')) {
            eval('function cassaCorrente(): int { $c = (int)($_SESSION["id_cassa"] ?? 1); return ($c >= 1 && $c <= 999) ? $c : 1; }');
        }

        $this->dir = sys_get_temp_dir() . '/stampa_scoping_' . getmypid() . '_' . uniqid();
        mkdir($this->dir, 0770, true);
        foreach (['SALSICCIA_FISCALE_MOCK', 'SALSICCIA_FISCALE_MOCK_FILE', 'SALSICCIA_FISCALE_QUEUE_FILE', 'SALSICCIA_FISCALE_SENT_FILE'] as $k) {
            $this->envBackup[$k] = getenv($k);
        }

        putenv('SALSICCIA_FISCALE_MOCK=1');
        putenv('SALSICCIA_FISCALE_MOCK_FILE=' . $this->dir . '/mock.xml');
        putenv('SALSICCIA_FISCALE_QUEUE_FILE=' . $this->dir . '/coda.log');
        putenv('SALSICCIA_FISCALE_SENT_FILE=' . $this->dir . '/inviati.log');
    }

    protected function tearDown(): void
    {
        $_GET = $this->getBackup;
        $_SESSION = is_array($this->sessionBackup) ? $this->sessionBackup : [];
        foreach ($this->envBackup as $k => $v) {
            if ($v === false) {
                putenv($k);
            } else {
                putenv($k . '=' . $v);
            }
        }

        foreach (['mock.xml', 'coda.log', 'inviati.log'] as $f) {
            if (is_file($this->dir . '/' . $f)) {
                unlink($this->dir . '/' . $f);
            }
        }

        if ($this->dir !== '' && is_dir($this->dir)) {
            rmdir($this->dir);
        }

        parent::tearDown();
    }

    /**
     * @return array{0:StampaScopingFakeMysqli}
     */
    private function db(): array
    {
        $ordini = array(
            101 => array('totale' => 17.5, 'id_cassa' => 7, 'chiuso' => '1', 'num_biglietti' => 3),
            202 => array('totale' => 99.99, 'id_cassa' => 33, 'chiuso' => '1', 'num_biglietti' => 2),
        );
        $aggregate = array(
            101 => array(array('iva' => 0.22, 'totale' => 17.5)),
            202 => array(array('iva' => 0.22, 'totale' => 99.99)),
        );
        return array(new StampaScopingFakeMysqli($ordini, $aggregate));
    }

    private function stampaHtml($db): string
    {
        ob_start();
        StampaController::stampa($db);
        return (string)ob_get_clean();
    }

    private function righeFile(string $nome): int
    {
        $f = $this->dir . '/' . $nome;
        if (!is_file($f)) {
            return 0;
        }

        $righe = file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return is_array($righe) ? count($righe) : 0;
    }

    private function emissioniMock(): int
    {
        $f = $this->dir . '/mock.xml';
        if (!is_file($f)) {
            return 0;
        }

        return substr_count((string)file_get_contents($f), '<Service>');
    }

    public function testAltraCassaZeroRigheZeroEmissioneZeroTotale(): void
    {
        [$db] = $this->db();
        $_GET = array('action' => 's', 'stampato' => 202, 'metodo' => 'contanti');

        $out = $this->stampaHtml($db);

        $stmt = $db->stmtPer('FROM ordini');
        $this->assertNotNull($stmt);
        $this->assertStringContainsString('id_cassa', $stmt->sql);
        $this->assertStringContainsString('chiuso', $stmt->sql);
        $this->assertSame('ii', $stmt->tipi);
        $this->assertSame(array(202, 7), $stmt->params);
        $this->assertStringNotContainsString('99,99', $out);
        $this->assertSame(0, $this->emissioniMock());
        $this->assertSame(0, $this->righeFile('inviati.log'));
        $this->assertNull($db->stmtPer('righe_ordini'));
    }

    public function testStessaCassaAppenaStampatoUnaEmissioneTotaleCorretto(): void
    {
        [$db] = $this->db();
        $_GET = array('action' => 's', 'stampato' => 101, 'metodo' => 'contanti');

        $out = $this->stampaHtml($db);

        $stmt = $db->stmtPer('FROM ordini');
        $this->assertNotNull($stmt);
        $this->assertStringContainsString('id_cassa', $stmt->sql);
        $this->assertSame('ii', $stmt->tipi);
        $this->assertSame(array(101, 7), $stmt->params);
        $this->assertStringContainsString('17,50', $out);
        $this->assertSame(1, $this->emissioniMock());
        $this->assertSame(1, $this->righeFile('inviati.log'));
        $this->assertNotNull($db->stmtPer('righe_ordini'));
    }
}
