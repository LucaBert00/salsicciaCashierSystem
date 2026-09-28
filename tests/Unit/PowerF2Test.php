<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;

// F2.4 #71: esitoTentativoPower + escapaComando (introdotte da F2.1 #68).
// functionsFrontend.inc NON e' richiedibile in isolamento (require dbConnect
// con die + sessione + gestisciAzioni a top-level, v. righe 5-70): le due sole
// funzioni pure vengono caricate estraendone il sorgente reale dal file ed
// evalutandolo. Nessun DB, nessuna rete, nessuna exec, mai modifiche al
// sorgente per accomodare il test.
final class PowerF2Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (array('escapaComando', 'esitoTentativoPower') as $fn) {
            if (function_exists($fn)) {
                continue;
            }
            $src = (string) file_get_contents(__DIR__ . '/../../functionsFrontend.inc');
            $m = array();
            if (!preg_match('/^function ' . $fn . '\b.*?\r?\n\}/ms', $src, $m)) {
                $this->fail('funzione ' . $fn . ' non trovata in functionsFrontend.inc');
            }
            eval($m[0] . "\n");
        }
    }

    public function testEsitoRiuscitoUptimeMinoreEta(): void
    {
        // Marker 10s fa, uptime 5s < eta 10s: macchina riavviata dopo il marker.
        $this->assertSame('riuscito', esitoTentativoPower(990, 1000, 5.0));
    }

    public function testEsitoFallitoOltreSoglia(): void
    {
        // Marker 200s fa, uptime 500s (nessun riavvio): eta 200 > soglia 180.
        $this->assertSame('fallito', esitoTentativoPower(800, 1000, 500.0));
    }

    public function testEsitoAttesaUptimeMaggioreEta(): void
    {
        // Marker 10s fa, uptime 500s: nessun riavvio ma entro soglia.
        $this->assertSame('attesa', esitoTentativoPower(990, 1000, 500.0));
    }

    public function testEsitoAttesaMarkerFuturo(): void
    {
        // eta < 0: orologio storto o marker scritto dopo, mai fallito.
        $this->assertSame('attesa', esitoTentativoPower(1010, 1000, 500.0));
        $this->assertSame('attesa', esitoTentativoPower(1010, 1000, null));
    }

    public function testEsitoUptimeNull(): void
    {
        // Senza /proc/uptime: solo la soglia decide.
        $this->assertSame('attesa', esitoTentativoPower(990, 1000, null));
        $this->assertSame('fallito', esitoTentativoPower(800, 1000, null));
    }

    public function testEsitoBoundarySoglia180(): void
    {
        // eta > 180 e' fallito, eta = 180 resta attesa (uptime null isola il ramo).
        $this->assertSame('attesa', esitoTentativoPower(820, 1000, null));
        $this->assertSame('fallito', esitoTentativoPower(819, 1000, null));
    }

    public function testEscapaQuotaOgniPezzo(): void
    {
        $atteso = escapeshellarg('/sbin/shutdown') . ' ' . escapeshellarg('-r') . ' ' . escapeshellarg('now');
        $this->assertSame($atteso, escapaComando('/sbin/shutdown -r now'));
    }

    public function testEscapaSpaziDoppiNessunArgomentoVuoto(): void
    {
        $atteso = escapeshellarg('/sbin/shutdown') . ' ' . escapeshellarg('-r') . ' ' . escapeshellarg('now');
        $ris = escapaComando('/sbin/shutdown  -r  now');
        $this->assertSame($atteso, $ris);
        $this->assertSame(3, count(explode(' ', $ris)));
        $this->assertStringNotContainsString("''", $ris);
    }
}
