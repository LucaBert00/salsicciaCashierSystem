<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;

// Rete di regressione sui quattro builder puri: niente DB, niente rete,
// niente hardware. Dati di esempio in tests/fixtures/.
final class PureBuildersTest extends TestCase
{
    public function testBuildXmlRaggruppaRepartiIvaEChiudeConContanti(): void
    {
        $righe = json_decode(
            (string) file_get_contents(__DIR__ . '/../fixtures/righe.json'),
            true
        );

        $xml = \Salsiccia\Fiscale\Fiscale::buildXml($righe, 'contanti');

        $this->assertStringContainsString('<cmd>=K</cmd>', $xml);
        $this->assertStringContainsString('<cmd>=R3/$1000</cmd>', $xml);
        $this->assertStringContainsString('<cmd>=R2/$550</cmd>', $xml);
        $this->assertStringContainsString('<cmd>=R1/$200</cmd>', $xml);
        $this->assertStringContainsString('<cmd>=T1</cmd>', $xml);
    }

    public function testBuildXmlCartaChiudeConT3EMetodoIgnotoRifiutato(): void
    {
        $righe = json_decode(
            (string) file_get_contents(__DIR__ . '/../fixtures/righe.json'),
            true
        );

        $this->assertStringContainsString('<cmd>=T3</cmd>', \Salsiccia\Fiscale\Fiscale::buildXml($righe, 'carta'));
        $this->assertSame('', \Salsiccia\Fiscale\Fiscale::buildXml($righe, 'buono'));
        $this->assertSame('', \Salsiccia\Fiscale\Fiscale::buildXml([], 'contanti'));
        $this->assertSame('', \Salsiccia\Fiscale\Fiscale::buildXml([['iva' => 0.99, 'totale' => 3.0]], 'contanti'));
    }

    public function testParseStatoOkKoEMalformato(): void
    {
        $ok = \Salsiccia\Fiscale\Fiscale::parseStato(
            (string) file_get_contents(__DIR__ . '/../fixtures/fiscale_ok.xml')
        );
        $this->assertTrue($ok['ok']);
        $this->assertSame(0, $ok['errorCode']);

        $ko = \Salsiccia\Fiscale\Fiscale::parseStato(
            (string) file_get_contents(__DIR__ . '/../fixtures/fiscale_errore.xml')
        );
        $this->assertFalse($ko['ok']);
        $this->assertSame(5, $ko['errorCode']);
        $this->assertSame(1, $ko['paperEnd']);

        $this->assertFalse(\Salsiccia\Fiscale\Fiscale::parseStato('non-xml')['ok']);
        $this->assertFalse(\Salsiccia\Fiscale\Fiscale::parseStato('<Service/>')['ok']);
    }

    public function testEtichettaContinuaZplSenzaDbNeStampante(): void
    {
        $righe = json_decode(
            (string) file_get_contents(__DIR__ . '/../fixtures/etichetta_righe.json'),
            true
        );

        $ret = \Salsiccia\Cassa\etichetta_continua(
            $righe,
            2,
            'Festa Salsiccia 15/08/26',
            'crediti fiera',
            '1',
            '15/08/26 12:00',
            'ZPL'
        );

        $this->assertSame(2, $ret['num_pezzi']);
        $this->assertStringContainsString('^XA', $ret['label']);
        $this->assertStringContainsString('TOTALE: 12.50', $ret['label']);
        $this->assertStringContainsString('^XZ', $ret['label']);
    }

    public function testEtichettaContinuaEplSenzaDbNeStampante(): void
    {
        $righe = json_decode(
            (string) file_get_contents(__DIR__ . '/../fixtures/etichetta_righe.json'),
            true
        );

        $ret = \Salsiccia\Cassa\etichetta_continua(
            $righe,
            2,
            'Festa Salsiccia 15/08/26',
            'crediti fiera',
            '1',
            '15/08/26 12:00',
            'EPL'
        );

        $this->assertSame(2, $ret['num_pezzi']);
        $this->assertStringContainsString('TOTALE: 12.50', $ret['label']);
        $this->assertStringContainsString("\nP1\n", $ret['label']);
    }

    public function testStatLimitiGiornoDura24OreDallOraDiCambio(): void
    {
        $this->assertSame(
            ['2026-01-15 05:00:00', '2026-01-16 05:00:00'],
            \Salsiccia\Stats\StatsData::limitiGiorno('2026-01-15', 5)
        );
        $this->assertSame(
            ['2026-01-31 23:00:00', '2026-02-01 23:00:00'],
            \Salsiccia\Stats\StatsData::limitiGiorno('2026-01-31', 23)
        );
        $this->assertSame(
            ['2026-01-15 00:00:00', '2026-01-16 00:00:00'],
            \Salsiccia\Stats\StatsData::limitiGiorno('2026-01-15', 0)
        );
    }
}
