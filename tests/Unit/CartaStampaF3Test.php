<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;

// F3.6 #66: mappa/normalizzazione/lettura setup carta (F3.1/F3.5).
// Porta di salsicciaStagisti test_carta_stampa.php (solo righe 13-86:
// il check nomi skill righe 88-91 e' interno stagisti, non portato).
// Normalizzazione su fixture inline, lettura sugli asset reali
// storage/printerCommand (F3.5, dipendenza reale non mock). Mai
// printer/CUPS/rete.
final class CartaStampaF3Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../env.inc';
    }

    public function testMappaEplSingoli(): void
    {
        $this->assertSame('cmd_LP2844_SINGOLI_EPL', cartaStampaFilePerStampante('LP2844', 'DIRETTA', 'EPL', false));
        $this->assertSame('cmd_TLP2844_SINGOLI_EPL', cartaStampaFilePerStampante('TLP2844', 'DIRETTA', 'EPL', false));
        $this->assertSame('cmd_GX420t_SINGOLI_EPL', cartaStampaFilePerStampante('GX420t', 'DIRETTA', 'EPL', false));
    }

    public function testMappaEplContinua(): void
    {
        $this->assertSame('cmd_LP2844_CONTINUA_EPL', cartaStampaFilePerStampante('LP2844', 'DIRETTA', 'EPL', true));
        $this->assertSame('cmd_TLP2844_CONTINUA_EPL', cartaStampaFilePerStampante('TLP2844', 'DIRETTA', 'EPL', true));
        $this->assertSame('cmd_GX420t_CONTINUA_EPL', cartaStampaFilePerStampante('GX420t', 'DIRETTA', 'EPL', true));
    }

    public function testMappaZpl(): void
    {
        $this->assertSame('cmd_ZD230_SINGOLI_ZPL', cartaStampaFilePerStampante('ZD230', 'RETE', 'ZPL', false));
        $this->assertSame('cmd_ZD230_CONTINUA_ZPL', cartaStampaFilePerStampante('ZD230', 'RETE', 'ZPL', true));
        $this->assertSame('cmd_ZD230_CONTINUA_ZPL', cartaStampaFilePerStampante('ZD230', 'DIRETTA', 'ZPL', true));
        $this->assertSame('cmd_GX420t_SINGOLI_ZPL', cartaStampaFilePerStampante('GX420t', 'DIRETTA', 'ZPL', false));
        $this->assertSame('cmd_GX420t_CONTINUA_ZPL', cartaStampaFilePerStampante('GX420t', 'DIRETTA', 'ZPL', true));
    }

    public function testMappaRifiutiFuoriGate(): void
    {
        $this->assertFalse(cartaStampaFilePerStampante('ZD230', 'RETE', 'EPL', true));
        $this->assertFalse(cartaStampaFilePerStampante('LP2844', 'DIRETTA', 'ZPL', false));
        $this->assertFalse(cartaStampaFilePerStampante('GX420t', 'RETE', 'ZPL', true));
        $this->assertFalse(cartaStampaFilePerStampante('Sconosciuta', 'DIRETTA', 'EPL', false));
        $this->assertFalse(cartaStampaFilePerStampante('../../etc', 'DIRETTA', 'EPL', false));

        foreach (array(array('LP2844', 'DIRETTA', 'EPL', false), array('LP2844', 'DIRETTA', 'EPL', true), array('ZD230', 'RETE', 'ZPL', true)) as $c) {
            $f = cartaStampaFilePerStampante($c[0], $c[1], $c[2], $c[3]);
            $this->assertNotSame('cmd.txt', $f, 'esclusi cmd.txt/tgz per ' . $c[0]);
            $this->assertSame(false, stripos((string) $f, '.tgz'), 'esclusi cmd.txt/tgz per ' . $c[0]);
        }
    }

    public function testNormalizzaEplInline(): void
    {
        $raw600 = ";fixture inline\nq600\nQ100,0\nP1\n";
        $this->assertNotFalse(strpos($raw600, 'q600'));
        $norm = cartaStampaNormalizzaContenuto($raw600, 'EPL', true);
        $this->assertNotFalse($norm);
        $this->assertSame(1, preg_match('/^q832\s*$/m', (string) $norm));
        $this->assertStringNotContainsString('q600', (string) $norm);

        $sing = cartaStampaNormalizzaContenuto(";fixture inline\nq600\nQ100,0\n", 'EPL', false);
        $this->assertNotFalse($sing);
        $this->assertSame(1, preg_match('/^q464\s*$/m', (string) $sing));

        $this->assertFalse(cartaStampaNormalizzaContenuto("casuale senza comandi\n", 'EPL', true));
    }

    public function testNormalizzaZplInline(): void
    {
        $rawZpl = "^XA\n^PW458\n^XZ\n";
        $sing = cartaStampaNormalizzaContenuto($rawZpl, 'ZPL', false);
        $this->assertNotFalse($sing);
        $this->assertStringContainsString('^PW464', (string) $sing);

        $cont = cartaStampaNormalizzaContenuto($rawZpl, 'ZPL', true);
        $this->assertNotFalse($cont);
        $this->assertStringContainsString('^PW832', (string) $cont);

        $this->assertFalse(cartaStampaNormalizzaContenuto("casuale senza comandi\n", 'ZPL', false));
    }

    public function testNormalizzaAssetRealiF35(): void
    {
        $dir = salsiccia_storage_path('printerCommand');

        $rawSing = @file_get_contents($dir . '/cmd_GX420t_SINGOLI_EPL');
        $this->assertNotFalse($rawSing);
        $normSing = cartaStampaNormalizzaContenuto($rawSing, 'EPL', false);
        $this->assertNotFalse($normSing);
        $this->assertSame(1, preg_match('/^q464\s*$/m', (string) $normSing));

        $rawZpl = @file_get_contents($dir . '/cmd_ZD230_SINGOLI_ZPL');
        $this->assertNotFalse($rawZpl);
        $normZpl = cartaStampaNormalizzaContenuto($rawZpl, 'ZPL', false);
        $this->assertNotFalse($normZpl);
        $this->assertStringContainsString('^PW464', (string) $normZpl);
        $this->assertStringContainsString('^PW832', (string) cartaStampaNormalizzaContenuto($rawZpl, 'ZPL', true));
    }

    public function testLeggiSetupRealeDieciCombo(): void
    {
        $baseDir = salsiccia_storage_path('printerCommand');
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
            $contenuto = cartaStampaLeggiSetup($a[0], $a[1], $a[2], $a[3], $err, $baseDir);
            $this->assertNotFalse($contenuto, 'setup ' . $a[0] . '/' . $a[2] . '/' . ($a[3] ? 'continua' : 'singoli'));
            $this->assertStringContainsString($a[4], (string) $contenuto, 'setup ' . $a[0] . '/' . $a[2] . '/' . ($a[3] ? 'continua' : 'singoli'));
            $this->assertSame('', $err, 'nessun errore per ' . $a[0]);
        }
    }

    public function testLeggiSetupDirInesistenteEFuoriGate(): void
    {
        $err = '';
        $this->assertFalse(cartaStampaLeggiSetup('LP2844', 'DIRETTA', 'EPL', false, $err, __DIR__ . '/printerCommand-vuota-inesistente'));
        $this->assertNotSame('', trim($err));

        $err = '';
        $this->assertFalse(cartaStampaLeggiSetup('ZD230', 'RETE', 'EPL', true, $err));
        $this->assertNotSame('', trim($err));
    }
}
