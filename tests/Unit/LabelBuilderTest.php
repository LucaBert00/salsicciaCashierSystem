<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;

// F4.3 #100 (punto 16, §6/§7/§9): rete test-first sui due builder money-critical
// ancora senza test (wrapping righe + riga etichetta). Scritti contro le globali
// in funzioni.inc prima dello spostamento; dopo la migrazione in
// src/Cassa/LabelBuilder.php passano senza modifiche alle attese (alias sottili).
final class LabelBuilderTest extends TestCase
{
    public function testTestoBigliettiUnaParola(): void
    {
        $this->assertSame(array('PANINO', ''), testo_biglietti('Panino'));
    }

    public function testTestoBigliettiDueParole(): void
    {
        $this->assertSame(array('PANINO', 'SALSICCIA'), testo_biglietti('Panino salsiccia'));
    }

    public function testTestoBigliettiDueParolePrimaLungaRestaVuoto(): void
    {
        $this->assertSame(array('', ''), testo_biglietti('Salsicciaextra Y'));
    }

    public function testTestoBigliettiTreParole(): void
    {
        $this->assertSame(array('PANINO CON', 'SALSICCIA'), testo_biglietti('Panino con salsiccia'));
        $this->assertSame(array('SALSICCIAEXTRA', 'CON PATATINE'), testo_biglietti('Salsicciaextra con patatine'));
    }

    public function testTestoBigliettiQuattroParoleTreRamiSenzaFallThroughT11(): void
    {
        // T11: senza break dopo case 4 il case 5 sovrascriverebbe (array[4] indefinito).
        $this->assertSame(array('A BB CC', 'DD'), testo_biglietti('A BB CC DD'));
        $this->assertSame(array('AAAA BBBBB', 'CCCCC DDDD'), testo_biglietti('AAAA BBBBB CCCCC DDDD'));
        $this->assertSame(array('AAAAAAAAAAAA', 'BBBB CCCC DDDD'), testo_biglietti('AAAAAAAAAAAA BBBB CCCC DDDD'));
    }

    public function testTestoBigliettiCinqueParoleEAccento(): void
    {
        $this->assertSame(array('A BB CC DD', 'EE'), testo_biglietti('A BB CC DD EE'));
        $this->assertSame(array("CAFFE'", ''), testo_biglietti('Caffè'));
    }

    private function parametri(string $tipo, string $olpp, int $qta, array $testo, int $poi, int $numPezzi = 0): array
    {
        return array(
            'now' => '15/08/26 12:00',
            'id' => 'N.7',
            'cassa' => '1',
            'credits' => 'CREDITS',
            'evento' => 'FESTA',
            't' => '',
            'quantita' => $qta,
            'testo' => $testo,
            'olpp' => $olpp,
            'num_pezzi' => $numPezzi,
            'print_order_id' => $poi,
            'tipo_stampante' => $tipo,
        );
    }

    public function testGetProductLabelZplOlppTUnaCopiaPerPezzo(): void
    {
        $ret = get_product_label($this->parametri('ZPL', 'T', 3, array('PANINO', ''), 1));
        $this->assertSame(3, $ret['num_pezzi']);
        $this->assertStringContainsString('^PQ3', $ret['label']);
        $this->assertStringContainsString('N.7 Cassa1', $ret['label']);
    }

    public function testGetProductLabelZplQuantitaUnoComeOlppT(): void
    {
        $ret = get_product_label($this->parametri('ZPL', 'F', 1, array('PANINO', ''), 1));
        $this->assertSame(1, $ret['num_pezzi']);
        $this->assertStringContainsString('^PQ1', $ret['label']);
    }

    public function testGetProductLabelZplOlppFCortoEtichettaUnica(): void
    {
        $ret = get_product_label($this->parametri('ZPL', 'F', 3, array('PANINO', ''), 0));
        $this->assertSame(1, $ret['num_pezzi']);
        $this->assertStringContainsString('3X PANINO', $ret['label']);
        $this->assertStringContainsString('^PQ1', $ret['label']);
        $this->assertStringNotContainsString('N.7', $ret['label']);
    }

    public function testGetProductLabelZplOlppFLungoFontRidotto(): void
    {
        $ret = get_product_label($this->parametri('ZPL', 'F', 3, array('SALSICCIAEXTRA', ''), 0));
        $this->assertSame(1, $ret['num_pezzi']);
        $this->assertStringContainsString('^ABN,32', $ret['label']);
        $this->assertStringContainsString('3X SALSICCIAEXTRA', $ret['label']);
    }

    public function testGetProductLabelEplOlppTUnaCopiaPerPezzo(): void
    {
        $ret = get_product_label($this->parametri('EPL', 'T', 2, array('PANINO', ''), 1));
        $this->assertSame(2, $ret['num_pezzi']);
        $this->assertStringContainsString("\nP2\n", $ret['label']);
        $this->assertStringContainsString('N.7 Cassa1', $ret['label']);
    }

    public function testGetProductLabelEplOlppFCortoEtichettaUnica(): void
    {
        $ret = get_product_label($this->parametri('EPL', 'F', 3, array('PANINO', ''), 0));
        $this->assertSame(1, $ret['num_pezzi']);
        $this->assertStringContainsString('3X PANINO', $ret['label']);
        $this->assertStringContainsString("\nP1\n", $ret['label']);
    }

    public function testGetProductLabelEplOlppFLungoFontRidottoMinuscola(): void
    {
        $ret = get_product_label($this->parametri('EPL', 'F', 3, array('SALSICCIAEXTRA', ''), 0));
        $this->assertSame(1, $ret['num_pezzi']);
        $this->assertStringContainsString('3x SALSICCIAEXTRA', $ret['label']);
        $this->assertStringContainsString('2,1,3,3', $ret['label']);
    }
}
