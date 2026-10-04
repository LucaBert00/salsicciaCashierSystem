<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

// F3 punto 13 #96: esitoTentativoPower + escapaComando (introdotte da F2.1 #68).
// functionsFrontend.inc e' richiedibile in isolamento da F3.2 #95 (nessun
// side-effect a top-level, init in bootstrap.php + flusso F1): require diretto.
// Nessun DB, nessuna rete, nessuna exec, mai modifiche al sorgente per
// accomodare il test.
/**
 * Processo separato: CassaViewTest/CassaControllerTest definiscono via eval
 * altre funzioni dello stesso file (stub + doppio handler); il require qui
 * sotto nello stesso processo le ridichiarerebbe.
 */
#[RunTestsInSeparateProcesses]
final class PowerF2Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../functionsFrontend.inc';
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
