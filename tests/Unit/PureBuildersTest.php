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

        $xml = fiscale_build_xml($righe, 'contanti');

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

        $this->assertStringContainsString('<cmd>=T3</cmd>', fiscale_build_xml($righe, 'carta'));
        $this->assertSame('', fiscale_build_xml($righe, 'buono'));
        $this->assertSame('', fiscale_build_xml([], 'contanti'));
        $this->assertSame('', fiscale_build_xml([['iva' => 0.99, 'totale' => 3.0]], 'contanti'));
    }

    public function testParseStatoOkKoEMalformato(): void
    {
        $ok = fiscale_parse_stato(
            (string) file_get_contents(__DIR__ . '/../fixtures/fiscale_ok.xml')
        );
        $this->assertTrue($ok['ok']);
        $this->assertSame(0, $ok['errorCode']);

        $ko = fiscale_parse_stato(
            (string) file_get_contents(__DIR__ . '/../fixtures/fiscale_errore.xml')
        );
        $this->assertFalse($ko['ok']);
        $this->assertSame(5, $ko['errorCode']);
        $this->assertSame(1, $ko['paperEnd']);

        $this->assertFalse(fiscale_parse_stato('non-xml')['ok']);
        $this->assertFalse(fiscale_parse_stato('<Service/>')['ok']);
    }

    public function testEtichettaContinuaZplSenzaDbNeStampante(): void
    {
        $righe = json_decode(
            (string) file_get_contents(__DIR__ . '/../fixtures/etichetta_righe.json'),
            true
        );

        $ret = etichetta_continua(
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

    public function testStatLimitiGiornoDura24OreDallOraDiCambio(): void
    {
        $this->assertSame(
            ['2026-01-15 05:00:00', '2026-01-16 05:00:00'],
            stat_limiti_giorno('2026-01-15', 5)
        );
        $this->assertSame(
            ['2026-01-31 23:00:00', '2026-02-01 23:00:00'],
            stat_limiti_giorno('2026-01-31', 23)
        );
        $this->assertSame(
            ['2026-01-15 00:00:00', '2026-01-16 00:00:00'],
            stat_limiti_giorno('2026-01-15', 0)
        );
    }
}
