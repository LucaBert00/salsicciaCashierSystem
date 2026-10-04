<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Printer\PrinterConfig;

// F2.3 #90: PrinterConfig::salva() riuso di impostaStampanteSelezionata()
// (functionsFrontend.inc, mappa §6 1214-1247). Pura logica: regex nome, gate,
// regola IP RETE + FILTER_VALIDATE_IP, JSON + LOCK_EX, bool come oggi.
// Mai rete/CUPS qui (raggiungibilita' solo con IP non valido, senza socket
// verso host reali); il file di selezione reale e' salvato e ripristinato.
final class PrinterConfigTest extends TestCase
{
    private string $file = '';
    private bool $avevaFile = false;
    private string $backup = '';

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../env.inc';
        $this->file = printer_selection_file();
        $this->avevaFile = is_readable($this->file);
        $this->backup = $this->avevaFile ? (string)@file_get_contents($this->file) : '';
    }

    protected function tearDown(): void
    {
        if ($this->avevaFile) {
            @file_put_contents($this->file, $this->backup, LOCK_EX);
        } else {
            @unlink($this->file);
        }
        parent::tearDown();
    }

    private function contenutoSelezione(): string|false
    {
        clearstatcache(true, $this->file);
        if (!is_readable($this->file)) {
            return false;
        }
        return @file_get_contents($this->file);
    }

    public function testNomeNonWhitelistatoRifiutatoFileInvariato(): void
    {
        $prima = $this->contenutoSelezione();

        $this->assertFalse(PrinterConfig::salva('evil name!', 'DIRETTA', 'ZPL'));
        $this->assertFalse(PrinterConfig::salva('', 'DIRETTA', 'ZPL'));
        $this->assertFalse(PrinterConfig::salva('Zebra;rm -rf', 'DIRETTA', 'ZPL'));

        $this->assertSame($prima, $this->contenutoSelezione());
    }

    public function testGateRifiutatoFileInvariato(): void
    {
        $prima = $this->contenutoSelezione();

        $this->assertFalse(PrinterConfig::salva('GX420t', 'RETE', 'ZPL'));
        $this->assertFalse(PrinterConfig::salva('ZD230', 'DIRETTA', 'EPL'));
        $this->assertFalse(PrinterConfig::salva('Sconosciuta', 'DIRETTA', 'ZPL'));

        $this->assertSame($prima, $this->contenutoSelezione());
    }

    public function testIpSoloReteEValidoFileInvariato(): void
    {
        $prima = $this->contenutoSelezione();

        $this->assertFalse(PrinterConfig::salva('ZD230', 'RETE', 'ZPL', 'not-an-ip'));
        $this->assertFalse(PrinterConfig::salva('GX420t', 'DIRETTA', 'ZPL', '192.168.0.30'));

        $this->assertSame($prima, $this->contenutoSelezione());
    }

    public function testValidaDirettaScriveJson(): void
    {
        $this->assertTrue(PrinterConfig::salva('GX420t', 'DIRETTA', 'EPL'));

        $d = json_decode((string)$this->contenutoSelezione(), true);
        $this->assertSame(
            array('name' => 'GX420t', 'connection' => 'DIRETTA', 'language' => 'EPL', 'ip' => ''),
            $d
        );
    }

    public function testValidaReteConIpScriveJson(): void
    {
        $this->assertTrue(PrinterConfig::salva('ZD230', 'RETE', 'ZPL', '192.168.0.30'));

        $d = json_decode((string)$this->contenutoSelezione(), true);
        $this->assertSame(
            array('name' => 'ZD230', 'connection' => 'RETE', 'language' => 'ZPL', 'ip' => '192.168.0.30'),
            $d
        );
    }

    public function testRaggiungibileReteIpNonValidoFalsoSenzaRete(): void
    {
        $this->assertFalse(PrinterConfig::raggiungibile('ZD230', 'RETE', 'not-an-ip'));
    }

    public function testWiringSwitchPrinterInvariato(): void
    {
        $tabella = (array)require __DIR__ . '/../../routes/cassa.php';

        $this->assertSame(
            array('handler' => 'salvaStampante', 'view' => 'switchPrinter', 'auth' => 'admin'),
            $tabella['switch_printer']
        );
    }
}
