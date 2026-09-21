<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Backoffice\Tabs\ProdottiTab;
use Salsiccia\Backoffice\VisualizzaStore;

// Issue #47: doppietti per SHOW COLUMNS + prepare/get_result della pipeline
// prodotti (i FakeMysqli/FakeStmt esistenti coprono solo db_exec, T30).
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di superficie mysqli in un solo file di test
final class ProdottiFakeResult
{
    public int $num_rows;

    private $row;

    private bool $fetched = false;

    public function __construct($row, int $numRows = 0)
    {
        $this->row = $row;
        $this->num_rows = $numRows;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function fetch_assoc(): array|false
    {
        if ($this->fetched) {
            return false;
        }
        $this->fetched = true;
        return $this->row ?? false;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di superficie mysqli in un solo file di test
final class ProdottiFakeStmt
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
    public function get_result(): ProdottiFakeResult
    {
        return new ProdottiFakeResult($this->row, $this->row === null ? 0 : 1);
    }

    public function close(): void
    {
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di superficie mysqli in un solo file di test
final class ProdottiFakeMysqli
{
    public array $queries = array();
    public array $prepares = array();
    public ?ProdottiFakeStmt $stmt = null;

    private object|false|null $queryResult;
    private bool $throwOnQuery;
    private $row;

    public function __construct(object|false|null $queryResult = null, $row = null, bool $throwOnQuery = false)
    {
        $this->queryResult = $queryResult;
        $this->row = $row;
        $this->throwOnQuery = $throwOnQuery;
    }

    public function query(string $sql): object|false
    {
        $this->queries[] = $sql;
        if ($this->throwOnQuery) {
            throw new \RuntimeException('connessione persa');
        }
        return $this->queryResult;
    }

    public function prepare(string $sql): object|false
    {
        $this->prepares[] = $sql;
        $this->stmt = new ProdottiFakeStmt($this->row);
        return $this->stmt;
    }
}

// Issue #47: barcode facoltativo — con colonna tutto come oggi meno il
// required, senza colonna lista/edit/save lavorano senza barcode.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi di superficie mysqli in un solo file di test
final class ProdottiBarcodeTest extends TestCase
{
    public function testHaColonnaVeraQuandoLaColonnaEsiste(): void
    {
        $db = new ProdottiFakeMysqli(new ProdottiFakeResult(null, 1));

        $this->assertTrue(VisualizzaStore::haColonna($db, 'prodotti', 'barcode'));
        $this->assertSame(array("SHOW COLUMNS FROM `prodotti` LIKE 'barcode'"), $db->queries);
    }

    public function testHaColonnaFalsaQuandoLaColonnaManca(): void
    {
        $db = new ProdottiFakeMysqli(new ProdottiFakeResult(null, 0));

        $this->assertFalse(VisualizzaStore::haColonna($db, 'prodotti', 'barcode'));
    }

    public function testHaColonnaFailOpenQuandoQueryFallisce(): void
    {
        $db = new ProdottiFakeMysqli(false);

        $this->assertTrue(VisualizzaStore::haColonna($db, 'prodotti', 'barcode'));
    }

    public function testHaColonnaFailOpenQuandoQueryLancia(): void
    {
        $db = new ProdottiFakeMysqli(null, null, true);

        $this->assertTrue(VisualizzaStore::haColonna($db, 'prodotti', 'barcode'));
    }

    public function testHaColonnaFailOpenSuIdentificatoriNonWhitelist(): void
    {
        $db = new ProdottiFakeMysqli(new ProdottiFakeResult(null, 0));

        $this->assertTrue(VisualizzaStore::haColonna($db, 'prodotti; DROP TABLE prodotti', 'barcode'));
        $this->assertTrue(VisualizzaStore::haColonna($db, 'prodotti', "barcode' OR '1'='1"));
        $this->assertSame(array(), $db->queries);
    }

    public function testConfigDefaultConColonnaInvariato(): void
    {
        $cfg = ProdottiTab::config();

        $this->assertStringContainsString('`barcode`', $cfg['select']);
        $this->assertArrayHasKey('barcode', $cfg['orders']);
        $this->assertContains('`barcode`', $cfg['search']);
    }

    public function testConfigSenzaColonnaToglieOgniRiferimentoBarcode(): void
    {
        $cfg = ProdottiTab::config(false);

        $this->assertStringNotContainsString('barcode', $cfg['select']);
        $this->assertArrayNotHasKey('barcode', $cfg['orders']);
        foreach ($cfg['search'] as $colonna) {
            $this->assertStringNotContainsString('barcode', $colonna);
        }
        $this->assertStringNotContainsString('barcode', $cfg['default']);
    }

    public function testSenzaColonnaOrderByBarcodeRicadeSulDefault(): void
    {
        $cfg = ProdottiTab::config(false);

        $this->assertSame(
            $cfg['default'],
            VisualizzaStore::resolveOrderBy($cfg['orders'], $cfg['default'], 'barcode', 'ASC')
        );
        $this->assertSame(
            '`descrizione_prod` ASC',
            VisualizzaStore::resolveOrderBy($cfg['orders'], $cfg['default'], 'descrizione_prod', 'ASC')
        );
    }

    // Nei test cassa_leggi_fiera() non esiste: fiera spenta (come il laptop
    // di fiera a cassa chiusa). Il default '-' vale solo per i nuovi prodotti.

    public function testSalvaConColonnaNuovoBarcodeVuotoUsaTrattino(): void
    {
        $db = new ProdottiFakeMysqli();
        $post = array('id' => '0', 'descrizione_prod' => 'SALSICCIA', 'prezzo' => '3.50', 'testo_biglietto' => '', 'olpp' => 'T', 'barcode' => '');

        $this->assertSame(array('redirect' => 'prodotti'), ProdottiTab::salva($db, $post, true));
        $this->assertStringContainsString('barcode', strtolower($db->prepares[0]));
        $this->assertSame('sddsss', $db->stmt->tipi);
        $this->assertSame('-', $db->stmt->params[5]);
    }

    public function testSalvaConColonnaModificaBarcodeVuotoAmmesso(): void
    {
        $db = new ProdottiFakeMysqli();
        $post = array('id' => '5', 'descrizione_prod' => 'SALSICCIA', 'prezzo' => '3.50', 'testo_biglietto' => '', 'olpp' => 'T', 'barcode' => '');

        $this->assertSame(array('redirect' => 'prodotti'), ProdottiTab::salva($db, $post, true));
        $this->assertStringContainsString('barcode = ?', $db->prepares[0]);
        $this->assertSame('sddsssi', $db->stmt->tipi);
        $this->assertSame('', $db->stmt->params[5]);
    }

    public function testSalvaRifiutaDescrizioneVuotaConSoloDescrizione(): void
    {
        $db = new ProdottiFakeMysqli();
        $post = array('id' => '0', 'descrizione_prod' => '   ', 'prezzo' => '3.50', 'testo_biglietto' => '', 'olpp' => 'T', 'barcode' => '123');

        $this->assertSame(array('msg' => 'DESCRIZIONE OBBLIGATORIA'), ProdottiTab::salva($db, $post, true));
        $this->assertSame(array(), $db->prepares);
    }

    public function testSalvaSenzaColonnaInsertSenzaBarcode(): void
    {
        $db = new ProdottiFakeMysqli();
        $post = array('id' => '0', 'descrizione_prod' => 'SALSICCIA', 'prezzo' => '3.50', 'testo_biglietto' => '', 'olpp' => 'T');

        $this->assertSame(array('redirect' => 'prodotti'), ProdottiTab::salva($db, $post, false));
        $this->assertStringNotContainsString('barcode', strtolower($db->prepares[0]));
        $this->assertSame('sddss', $db->stmt->tipi);
    }

    public function testSalvaSenzaColonnaUpdateSenzaBarcode(): void
    {
        $db = new ProdottiFakeMysqli();
        $post = array('id' => '5', 'descrizione_prod' => 'SALSICCIA', 'prezzo' => '3.50', 'testo_biglietto' => '', 'olpp' => 'T');

        $this->assertSame(array('redirect' => 'prodotti'), ProdottiTab::salva($db, $post, false));
        $this->assertStringNotContainsString('barcode', strtolower($db->prepares[0]));
        $this->assertSame('sddssi', $db->stmt->tipi);
    }

    public function testSalvaSenzaColonnaRifiutaDescrizioneVuota(): void
    {
        $db = new ProdottiFakeMysqli();
        $post = array('id' => '0', 'descrizione_prod' => '', 'prezzo' => '3.50', 'testo_biglietto' => '', 'olpp' => 'T');

        $this->assertSame(array('msg' => 'DESCRIZIONE OBBLIGATORIA'), ProdottiTab::salva($db, $post, false));
        $this->assertSame(array(), $db->prepares);
    }

    public function testCaricaModificaConColonnaLeggeBarcode(): void
    {
        $riga = array('id_prodotto' => 5, 'descrizione_prod' => 'X', 'prezzo' => 1.0, 'iva' => 0.22, 'testo_biglietto' => '', 'olpp' => 'T', 'barcode' => '123');
        $db = new ProdottiFakeMysqli(null, $riga);

        $this->assertSame($riga, ProdottiTab::caricaModifica($db, array('edit' => '5'), true));
        $this->assertStringContainsString('barcode', $db->prepares[0]);
    }

    public function testCaricaModificaSenzaColonnaDefaultVuoto(): void
    {
        $riga = array('id_prodotto' => 5, 'descrizione_prod' => 'X', 'prezzo' => 1.0, 'iva' => 0.22, 'testo_biglietto' => '', 'olpp' => 'T');
        $db = new ProdottiFakeMysqli(null, $riga);

        $attesa = $riga + array('barcode' => '');
        $this->assertSame($attesa, ProdottiTab::caricaModifica($db, array('edit' => '5'), false));
        $this->assertStringNotContainsString('barcode', strtolower($db->prepares[0]));
    }
}
