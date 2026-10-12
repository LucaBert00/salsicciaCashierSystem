<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\OrderService;
use Salsiccia\Fiscale\Fiscale;

// REL-03 #130 TDD: aritmetica in centesimi interi, mai binary float per $cent.
// Scelta centesimi (zero estensioni) esplicita vs BCMath: nessuna estensione
// richiesta, aritmetica esatta su stringhe DECIMAL da mysqli.
final class DecimalCentesimiTest extends TestCase
{
    public static function casiDecimali(): array
    {
        return array(
            'otto e venti' => array('8.20', 820),
            'un centesimo' => array('0.01', 1),
            'uno e quindici' => array('1.15', 115),
            'sette centesimi' => array('0.07', 7),
            'diciannove e novantanove' => array('19.99', 1999),
            'mille e oltre' => array('1234.56', 123456),
            'dodici e cinquanta' => array('12.50', 1250),
        );
    }

    // Documenta il difetto: il raw binary float di 8.20*100 non e esatto.
    // 8.20*100 = 819.9999999999998863, (int)(8.20*100) = 819 (sbagliato).
    public function testFloatGrezzoNonEsatto(): void
    {
        $raw = 8.20 * 100;
        $this->assertNotEquals(820.0, $raw, 'il raw binary float deve mostrare la deriva');
        $this->assertSame(819, (int)$raw, '(int) senza round tronca a 819');
        $this->assertSame('8.19999999999999929', sprintf('%.17F', (float)'8.20'));
    }

    /**
     * @dataProvider casiDecimali
     */
    public function testCentesimiDaStringaEsattiOrderService(string $prezzo, int $attesi): void
    {
        $this->assertSame($attesi, OrderService::centesimiDaPrezzo($prezzo));
    }

    /**
     * @dataProvider casiDecimali
     */
    public function testCentesimiDaStringaEsattiFiscale(string $totale, int $attesi): void
    {
        $this->assertSame($attesi, Fiscale::centesimiDaTotale($totale));
    }

    // buildXml con stringhe DECIMAL deve dare gli stessi centesimi, senza float.
    public function testBuildXmlConStringheDecimali(): void
    {
        $xml = Fiscale::buildXml(array(array('iva' => '0.22', 'totale' => '8.20')), 'contanti');
        $this->assertStringContainsString('<cmd>=R3/$820</cmd>', $xml);
    }
}
