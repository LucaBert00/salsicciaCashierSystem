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

    // REL-04 #126: golden PRIMA della card degustazione (builder puro).
    // La funzione chiama date("d/m/y H:i"): output mai byte-stabile tra run,
    // quindi il timestamp e normalizzato a <NOW> (stessa idea del token fisso
    // TOK128 in Maint02GoldenTest). Il resto e byte-identico: date hardcode
    // 16 Ottobbre 2025, sushitalia.com, 30ml, P1/C, raddoppio qta>1.
    private function normalizzaCard(string $s): string
    {
        return (string) preg_replace('/\d{2}\/\d{2}\/\d{2} \d{2}:\d{2}/', '<NOW>', $s);
    }

    public function testGeneraCardDegustazioneGoldenQtaUnoByteIdentico(): void
    {
        $att1 = 'N
A832,600,2,3,2,3,R,"     DEGUSTAZIONE 1 SAKE         "
X475,520,10,355,400
A800,370,2,2,2,2,N,"Il presente biglietto da diritto"
A800,330,2,2,2,2,N,"alla degustazione di 1 sake ed"
A800,290,2,2,2,2,N,"e\' valido per tutta la durata "
A800,250,2,2,2,2,N,"dell\'evento 16 Ottobbre 2025"
A800,190,2,4,1,1,N,"  La quantita\' di ciascun assaggio e\' di 30ml"
A800,130,2,3,1,1,N,"             Stampato il  <NOW>"
A800,90,2,2,1,1,N,"(La vendita di bevande alcoliche e\' vietata ai minori di 18 anni)"
A832,50,2,2,2,2,R,"         www.sushitalia.com             "
P1
C
';
        $att2 = 'N
A832,600,2,3,2,3,R,"     DEGUSTAZIONE 2 SAKE         "
X600,520,10,480,400
X400,520,10,280,400
A800,370,2,2,2,2,N,"Il presente biglietto da diritto"
A800,330,2,2,2,2,N,"alla degustazione di 2 sake ed"
A800,290,2,2,2,2,N,"e\' valido per tutta la durata "
A800,250,2,2,2,2,N,"dell\'evento 16 Ottobbre 2025"
A800,190,2,4,1,1,N,"  La quantita\' di ciascun assaggio e\' di 30ml"
A800,130,2,3,1,1,N,"             Stampato il  <NOW>"
A800,90,2,2,1,1,N,"(La vendita di bevande alcoliche e\' vietata ai minori di 18 anni)"
A832,50,2,2,2,2,R,"         www.sushitalia.com             "
P1
C
';
        $att5 = 'N
A832,600,2,3,2,3,R,"     DEGUSTAZIONE 5 SAKE         "
X770,520,10,650,400
X620,520,10,500,400
X470,520,10,350,400
X320,520,10,200,400
X170,520,10,50,400
A800,370,2,2,2,2,N,"Il presente biglietto da diritto"
A800,330,2,2,2,2,N,"alla degustazione di 5 sake ed"
A800,290,2,2,2,2,N,"e\' valido per tutta la durata "
A800,250,2,2,2,2,N,"dell\'evento 16 Ottobbre 2025"
A800,190,2,4,1,1,N,"  La quantita\' di ciascun assaggio e\' di 30ml"
A800,130,2,3,1,1,N,"             Stampato il  <NOW>"
A800,90,2,2,1,1,N,"(La vendita di bevande alcoliche e\' vietata ai minori di 18 anni)"
A832,50,2,2,2,2,R,"         www.sushitalia.com             "
P1
C
';
        foreach (array(1 => $att1, 2 => $att2, 5 => $att5) as $numero => $atteso) {
            $grezzo = \Salsiccia\Cassa\generaCardDegustazione($numero, 1);
            $this->assertIsString($grezzo, 'card ' . $numero . ' ritorna stringa');
            $this->assertSame(1, preg_match('/\d{2}\/\d{2}\/\d{2} \d{2}:\d{2}/', $grezzo));
            $this->assertSame($atteso, $this->normalizzaCard($grezzo));
            $this->assertStringContainsString('DEGUSTAZIONE ' . $numero . ' SAKE', $grezzo);
            $this->assertStringContainsString('30ml', $grezzo);
            $this->assertStringContainsString('www.sushitalia.com', $grezzo);
            $this->assertStringContainsString('16 Ottobbre 2025', $grezzo);
            $this->assertStringContainsString("\nP1\n", $grezzo);
            $this->assertStringEndsWith("C\n", $grezzo);
        }
    }

    public function testGeneraCardDegustazioneRaddoppioQtaOltreUno(): void
    {
        $uno = $this->normalizzaCard((string) \Salsiccia\Cassa\generaCardDegustazione(1, 1));
        $due = $this->normalizzaCard((string) \Salsiccia\Cassa\generaCardDegustazione(1, 2));
        $this->assertSame($uno . $uno, $due);
        $this->assertSame(2, substr_count($due, 'www.sushitalia.com'));
        $this->assertSame(2, substr_count($due, "\nP1\n"));
        $tre = $this->normalizzaCard((string) \Salsiccia\Cassa\generaCardDegustazione(2, 3));
        $uno2 = $this->normalizzaCard((string) \Salsiccia\Cassa\generaCardDegustazione(2, 1));
        $this->assertSame(strlen($uno2) * 4, strlen($tre));
        $this->assertSame(4, substr_count($tre, 'www.sushitalia.com'));
    }

    public function testGeneraCardDegustazioneNonScriveSuDisco(): void
    {
        $f = \Salsiccia\Support\Storage::path('labelCards');
        if (is_file($f)) {
            unlink($f);
        }
        \Salsiccia\Cassa\generaCardDegustazione(1, 1);
        $this->assertFileDoesNotExist($f);
    }

    public function testEtichettaContinuaEplConCardRitornaCardsSenzaScrittura(): void
    {
        $f = \Salsiccia\Support\Storage::path('labelCards');
        if (is_file($f)) {
            unlink($f);
        }
        $righe = array(
            array('id_ordine' => 7, 'tot_ord' => 30.00, 'tipo' => 'cibo', 'testo' => 'Degustazione 1', 'quantita' => 1, 'totale' => 10.00, 'id_prodotto' => 14),
            array('id_ordine' => 7, 'tot_ord' => 30.00, 'tipo' => 'cibo', 'testo' => 'Degustazione 2', 'quantita' => 2, 'totale' => 10.00, 'id_prodotto' => 15),
            array('id_ordine' => 7, 'tot_ord' => 30.00, 'tipo' => 'cibo', 'testo' => 'Degustazione 5', 'quantita' => 1, 'totale' => 10.00, 'id_prodotto' => 16),
        );
        $ret = \Salsiccia\Cassa\etichetta_continua($righe, 3, 'Festa Salsiccia 15/08/26', 'crediti fiera', '1', '15/08/26 12:00', 'EPL');
        $this->assertSame(3, $ret['num_pezzi']);
        $this->assertStringContainsString('TOTALE: 30.00', $ret['label']);
        $this->assertArrayHasKey('cards', $ret);
        $this->assertCount(3, $ret['cards']);
        $this->assertStringContainsString('DEGUSTAZIONE 1 SAKE', $ret['cards'][0]);
        $this->assertStringContainsString('DEGUSTAZIONE 2 SAKE', $ret['cards'][1]);
        $this->assertStringContainsString('DEGUSTAZIONE 5 SAKE', $ret['cards'][2]);
        $this->assertFileDoesNotExist($f);
    }

    public function testTimeoutLprClampStessoRangeFiscale(): void
    {
        putenv('SALSICCIA_LPR_TIMEOUT');
        $this->assertSame(10, \Salsiccia\Cassa\PrintService::timeoutLpr());
        putenv('SALSICCIA_LPR_TIMEOUT=0');
        $this->assertSame(1, \Salsiccia\Cassa\PrintService::timeoutLpr());
        putenv('SALSICCIA_LPR_TIMEOUT=99');
        $this->assertSame(30, \Salsiccia\Cassa\PrintService::timeoutLpr());
        putenv('SALSICCIA_LPR_TIMEOUT');
    }

    public function testEseguiComandoVeloceOkELentoTimeout(): void
    {
        $veloce = \Salsiccia\Cassa\PrintService::eseguiComando(PHP_BINARY . ' -r "exit(0);"', 5);
        $this->assertFalse($veloce['timeout']);
        $this->assertSame(0, $veloce['rc']);
        $t0 = microtime(true);
        $lento = \Salsiccia\Cassa\PrintService::eseguiComando(PHP_BINARY . ' -r "sleep(5);"', 1);
        $dt = microtime(true) - $t0;
        $this->assertTrue($lento['timeout']);
        $this->assertLessThan(4.0, $dt);
    }

    public function testSpedisciCardNomeNonWhitelistatoEsitoGestito(): void
    {
        $ok = \Salsiccia\Cassa\PrintService::spedisciCard('N
P1
C
', '../../etc', 'DIRETTA');
        $this->assertFalse($ok);
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
