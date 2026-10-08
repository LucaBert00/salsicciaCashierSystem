<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Printer\PaperSetup;
use Salsiccia\Support\Storage;

// F3.6 #66: mappa/normalizzazione/lettura setup carta (F3.1/F3.5).
// Porta di salsicciaStagisti test_carta_stampa.php (solo righe 13-86:
// il check nomi skill righe 88-91 e' interno stagisti, non portato).
// Normalizzazione su fixture inline, lettura sugli asset reali
// storage/printerCommand (F3.5, dipendenza reale non mock). Mai
// printer/CUPS/rete.
final class CartaStampaF3Test extends TestCase
{
    public function testMappaEplSingoli(): void
    {
        $this->assertSame('cmd_LP2844_SINGOLI_EPL', PaperSetup::filePerStampante('LP2844', 'DIRETTA', 'EPL', false));
        $this->assertSame('cmd_TLP2844_SINGOLI_EPL', PaperSetup::filePerStampante('TLP2844', 'DIRETTA', 'EPL', false));
        $this->assertSame('cmd_GX420t_SINGOLI_EPL', PaperSetup::filePerStampante('GX420t', 'DIRETTA', 'EPL', false));
    }

    public function testMappaEplContinua(): void
    {
        $this->assertSame('cmd_LP2844_CONTINUA_EPL', PaperSetup::filePerStampante('LP2844', 'DIRETTA', 'EPL', true));
        $this->assertSame('cmd_TLP2844_CONTINUA_EPL', PaperSetup::filePerStampante('TLP2844', 'DIRETTA', 'EPL', true));
        $this->assertSame('cmd_GX420t_CONTINUA_EPL', PaperSetup::filePerStampante('GX420t', 'DIRETTA', 'EPL', true));
    }

    public function testMappaZpl(): void
    {
        $this->assertSame('cmd_ZD230_SINGOLI_ZPL', PaperSetup::filePerStampante('ZD230', 'RETE', 'ZPL', false));
        $this->assertSame('cmd_ZD230_CONTINUA_ZPL', PaperSetup::filePerStampante('ZD230', 'RETE', 'ZPL', true));
        $this->assertSame('cmd_ZD230_CONTINUA_ZPL', PaperSetup::filePerStampante('ZD230', 'DIRETTA', 'ZPL', true));
        $this->assertSame('cmd_GX420t_SINGOLI_ZPL', PaperSetup::filePerStampante('GX420t', 'DIRETTA', 'ZPL', false));
        $this->assertSame('cmd_GX420t_CONTINUA_ZPL', PaperSetup::filePerStampante('GX420t', 'DIRETTA', 'ZPL', true));
    }

    public function testMappaRifiutiFuoriGate(): void
    {
        $this->assertFalse(PaperSetup::filePerStampante('ZD230', 'RETE', 'EPL', true));
        $this->assertFalse(PaperSetup::filePerStampante('LP2844', 'DIRETTA', 'ZPL', false));
        $this->assertFalse(PaperSetup::filePerStampante('GX420t', 'RETE', 'ZPL', true));
        $this->assertFalse(PaperSetup::filePerStampante('Sconosciuta', 'DIRETTA', 'EPL', false));
        $this->assertFalse(PaperSetup::filePerStampante('../../etc', 'DIRETTA', 'EPL', false));

        foreach (array(array('LP2844', 'DIRETTA', 'EPL', false), array('LP2844', 'DIRETTA', 'EPL', true), array('ZD230', 'RETE', 'ZPL', true)) as $c) {
            $f = PaperSetup::filePerStampante($c[0], $c[1], $c[2], $c[3]);
            $this->assertNotSame('cmd.txt', $f, 'esclusi cmd.txt/tgz per ' . $c[0]);
            $this->assertSame(false, stripos((string) $f, '.tgz'), 'esclusi cmd.txt/tgz per ' . $c[0]);
        }
    }

    public function testNormalizzaEplInline(): void
    {
        $raw600 = ";fixture inline\nq600\nQ100,0\nP1\n";
        $this->assertNotFalse(strpos($raw600, 'q600'));
        $norm = PaperSetup::normalizzaContenuto($raw600, 'EPL', true);
        $this->assertNotFalse($norm);
        $this->assertSame(1, preg_match('/^q832\s*$/m', (string) $norm));
        $this->assertStringNotContainsString('q600', (string) $norm);

        $sing = PaperSetup::normalizzaContenuto(";fixture inline\nq600\nQ100,0\n", 'EPL', false);
        $this->assertNotFalse($sing);
        $this->assertSame(1, preg_match('/^q464\s*$/m', (string) $sing));

        $this->assertFalse(PaperSetup::normalizzaContenuto("casuale senza comandi\n", 'EPL', true));
    }

    public function testNormalizzaZplInline(): void
    {
        $rawZpl = "^XA\n^PW458\n^XZ\n";
        $sing = PaperSetup::normalizzaContenuto($rawZpl, 'ZPL', false);
        $this->assertNotFalse($sing);
        $this->assertStringContainsString('^PW464', (string) $sing);

        $cont = PaperSetup::normalizzaContenuto($rawZpl, 'ZPL', true);
        $this->assertNotFalse($cont);
        $this->assertStringContainsString('^PW832', (string) $cont);

        $this->assertFalse(PaperSetup::normalizzaContenuto("casuale senza comandi\n", 'ZPL', false));
    }

    public function testNormalizzaAssetRealiF35(): void
    {
        $dir = Storage::path('printerCommand');

        $rawSing = @file_get_contents($dir . '/cmd_GX420t_SINGOLI_EPL');
        $this->assertNotFalse($rawSing);
        $normSing = PaperSetup::normalizzaContenuto($rawSing, 'EPL', false);
        $this->assertNotFalse($normSing);
        $this->assertSame(1, preg_match('/^q464\s*$/m', (string) $normSing));

        $rawZpl = @file_get_contents($dir . '/cmd_ZD230_SINGOLI_ZPL');
        $this->assertNotFalse($rawZpl);
        $normZpl = PaperSetup::normalizzaContenuto($rawZpl, 'ZPL', false);
        $this->assertNotFalse($normZpl);
        $this->assertStringContainsString('^PW464', (string) $normZpl);
        $this->assertStringContainsString('^PW832', (string) PaperSetup::normalizzaContenuto($rawZpl, 'ZPL', true));
    }

    public function testLeggiSetupRealeDieciCombo(): void
    {
        $baseDir = Storage::path('printerCommand');
        $attesi = array(
            array('ZD230', 'RETE', 'ZPL', false, '^PW464'),
            array('ZD230', 'RETE', 'ZPL', true, '^PW832'),
            array('GX420t', 'DIRETTA', 'ZPL', false, '^PW464'),
            array('GX420t', 'DIRETTA', 'ZPL', true, '^PW832'),
            array('GX420t', 'DIRETTA', 'EPL', false, 'q464'),
            array('GX420t', 'DIRETTA', 'EPL', true, 'q832'),
            array('TLP2844', 'DIRETTA', 'EPL', false, 'q464'),
            array('TLP2844', 'DIRETTA', 'EPL', true, 'q832'),
            array('LP2844', 'DIRETTA', 'EPL', false, 'q464'),
            array('LP2844', 'DIRETTA', 'EPL', true, 'q832'),
        );
        foreach ($attesi as $a) {
            $err = '';
            $contenuto = PaperSetup::leggiSetup($a[0], $a[1], $a[2], $a[3], $err, $baseDir);
            $this->assertNotFalse($contenuto, 'setup ' . $a[0] . '/' . $a[2] . '/' . ($a[3] ? 'continua' : 'singoli'));
            $this->assertStringContainsString($a[4], (string) $contenuto, 'setup ' . $a[0] . '/' . $a[2] . '/' . ($a[3] ? 'continua' : 'singoli'));
            $this->assertSame('', $err, 'nessun errore per ' . $a[0]);
        }
    }

    public function testLeggiSetupDirInesistenteEFuoriGate(): void
    {
        $err = '';
        $this->assertFalse(PaperSetup::leggiSetup('LP2844', 'DIRETTA', 'EPL', false, $err, __DIR__ . '/printerCommand-vuota-inesistente'));
        $this->assertNotSame('', trim($err));

        $err = '';
        $this->assertFalse(PaperSetup::leggiSetup('ZD230', 'RETE', 'EPL', true, $err));
        $this->assertNotSame('', trim($err));
    }
}
