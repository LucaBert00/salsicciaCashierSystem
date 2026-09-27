<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;

// F4.6 #56: gate/parser/stato F4 con fixture lpstat inline, mai CUPS/rete.
// Solo funzioni pure di env.inc (F4.1/F4.2); nessun exec, socket o storage reale.
final class PrinterF4GateParserStatoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../env.inc';
    }

    public function testGateNomiFinaliAmmessi(): void
    {
        $this->assertTrue(printer_gate_allowed('ZD230', 'DIRETTA', 'ZPL'));
        $this->assertTrue(printer_gate_allowed('ZD230', 'RETE', 'ZPL'));
        $this->assertTrue(printer_gate_allowed('GX420t', 'DIRETTA', 'ZPL'));
        $this->assertTrue(printer_gate_allowed('GX420t', 'DIRETTA', 'EPL'));
        $this->assertTrue(printer_gate_allowed('TLP2844', 'DIRETTA', 'EPL'));
        $this->assertTrue(printer_gate_allowed('LP2844', 'DIRETTA', 'EPL'));
    }

    public function testGateRifiutiInclusiVecchiZebra(): void
    {
        foreach (array('Zebra_Multi', 'Zebra_Net', 'Zebra_GX420t', 'Zebra_EPL_1', 'Zebra_EPL_2', 'Zebra_Qualsiasi') as $vecchio) {
            $this->assertFalse(printer_gate_allowed($vecchio, 'DIRETTA', 'ZPL'), $vecchio);
            $this->assertFalse(printer_is_continuous($vecchio, 'DIRETTA', 'ZPL'), $vecchio);
        }
        $this->assertFalse(printer_gate_allowed('GX420t', 'RETE', 'ZPL'));
        $this->assertFalse(printer_gate_allowed('ZD230', 'DIRETTA', 'EPL'));
        $this->assertFalse(printer_gate_allowed('TLP2844', 'RETE', 'EPL'));
        $this->assertFalse(printer_gate_allowed('TLP2844', 'DIRETTA', 'ZPL'));
        $this->assertFalse(printer_gate_allowed('', 'DIRETTA', 'ZPL'));
        $this->assertTrue(printer_is_continuous('ZD230', 'RETE', 'ZPL'));
        $this->assertTrue(printer_is_continuous('GX420t', 'DIRETTA', 'EPL'));
        $this->assertFalse(printer_is_continuous('TLP2844', 'DIRETTA', 'EPL'));
        $this->assertFalse(printer_is_continuous('LP2844', 'DIRETTA', 'EPL'));
    }

    public function testParserIdleEPrintingConDeviceAcceptingJobs(): void
    {
        $idle = printer_lpstat_parse_stato('GX420t', array(
            'scheduler is running',
            'system default destination: GX420t',
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), 'GX420t');
        $this->assertSame('idle', $idle['state']);
        $this->assertTrue((bool) $idle['enabled']);
        $this->assertTrue((bool) $idle['accepting']);
        $this->assertStringStartsWith('usb://', $idle['device']);
        $this->assertSame(0, $idle['jobs']);
        $this->assertTrue((bool) $idle['predefinita']);

        $prn = printer_lpstat_parse_stato('ZD230', array(
            'device for ZD230: usb://Zebra/ZD230?serial=ABC123',
            'ZD230 accepting requests since Wed Oct 29 12:27:19 2025',
            'ZD230-42 Zebra 1024 Wed Oct 29 12:31:00 2025',
            'printer ZD230 now printing ZD230-42.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $this->assertSame('printing', $prn['state']);
        $this->assertTrue((bool) $prn['enabled']);
        $this->assertTrue((bool) $prn['accepting']);
        $this->assertSame(1, $prn['jobs']);
        $this->assertFalse((bool) $prn['predefinita']);
    }

    public function testParserDisabledUnknownENonAccepting(): void
    {
        $dis = printer_lpstat_parse_stato('TLP2844', array(
            'device for TLP2844: usb://Zebra/TLP2844?serial=X',
            'TLP2844 not accepting requests since Wed Oct 29 12:30:00 2025',
            'printer TLP2844 disabled since Wed Oct 29 12:30:00 2025 - Paused',
        ), '');
        $this->assertSame('disabled', $dis['state']);
        $this->assertFalse((bool) $dis['enabled']);
        $this->assertFalse((bool) $dis['accepting']);
        $this->assertSame('Paused', $dis['reason']);

        $unk = printer_lpstat_parse_stato('LP2844', array('printer LP2844 unknown'), '');
        $this->assertSame('unknown', $unk['state']);
        $this->assertFalse((bool) $unk['enabled']);
        $this->assertNotSame('', trim((string) $unk['reason']));

        $assente = printer_lpstat_parse_stato('GX420t', array('scheduler is running'), '');
        $this->assertSame('unknown', $assente['state']);
        $this->assertNotSame('', trim((string) $assente['reason']));
    }

    public function testFusioneConfermaNeutraTieneBase(): void
    {
        $base = printer_lpstat_parse_stato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $base['verificabile'] = true;

        $idle = printer_lpstat_parse_stato('GX420t', array(
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $prn = printer_lpstat_parse_stato('GX420t', array(
            'printer GX420t now printing GX420t-42.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $this->assertSame($base, printer_lpstat_applica_conferma_p($base, $idle));
        $this->assertSame($base, printer_lpstat_applica_conferma_p($base, $prn));
        $this->assertSame($base, printer_lpstat_applica_conferma_p($base, null));
        $this->assertSame($base, printer_lpstat_applica_conferma_p($base, array()));
    }

    public function testFusioneDisabledUnknownVinceRosso(): void
    {
        $base = printer_lpstat_parse_stato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $base['verificabile'] = true;

        $dis = printer_lpstat_parse_stato('GX420t', array(
            'printer GX420t disabled since Wed Oct 29 12:30:00 2025 - Paused',
        ), '');
        $rosso = printer_lpstat_applica_conferma_p($base, $dis);
        $this->assertSame('disabled', $rosso['state']);
        $this->assertFalse((bool) $rosso['enabled']);
        $this->assertSame('Paused', $rosso['reason']);
        $this->assertFalse(printer_stato_locale_ok($rosso, true));

        $assente = printer_lpstat_parse_stato('GX420t', array(), '');
        $rosso2 = printer_lpstat_applica_conferma_p($base, $assente);
        $this->assertFalse(printer_stato_locale_ok($rosso2, true));
        $this->assertNotSame('', trim((string) $rosso2['reason']));
    }

    public function testStatoLocaleOkIdleEPrinting(): void
    {
        $idle = printer_lpstat_parse_stato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $idle['verificabile'] = true;
        $this->assertTrue(printer_stato_locale_ok($idle, true));

        $prn = printer_lpstat_parse_stato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'GX420t-42 Zebra 1024 Wed Oct 29 12:31:00 2025',
            'printer GX420t now printing GX420t-42.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $prn['verificabile'] = true;
        $this->assertTrue(printer_stato_locale_ok($prn, true));
    }

    public function testStatoLocaleKoDeviceUsb(): void
    {
        $base = printer_lpstat_parse_stato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $base['verificabile'] = true;

        $noDev = $base;
        $noDev['device'] = '';
        $this->assertFalse(printer_stato_locale_ok($noDev, true));

        $nullDev = $base;
        $nullDev['device'] = 'file:///dev/null';
        $this->assertFalse(printer_stato_locale_ok($nullDev, true));

        $this->assertFalse(printer_stato_locale_ok($base, false));

        $noVer = $base;
        $noVer['verificabile'] = false;
        $this->assertFalse(printer_stato_locale_ok($noVer, true));

        $noAcc = $base;
        $noAcc['accepting'] = false;
        $this->assertFalse(printer_stato_locale_ok($noAcc, true));
    }

    public function testUsbPresenzaSeriale(): void
    {
        $dev = 'usb://Zebra Technologies/ZTC GX420t?serial=31J115301224';
        $this->assertTrue(printer_usb_presente_da_evidenza(
            array(),
            array('direct usb://Zebra Technologies/ZTC GX420t?serial=31J115301224 "Zebra"'),
            $dev
        ));
        $this->assertFalse(printer_usb_presente_da_evidenza(
            array(),
            array('direct usb://Zebra Technologies/ZTC GX420t?serial=ALTRO123 "Zebra"'),
            $dev
        ));
    }

    public function testUsbPresenzaGenericaEAssenza(): void
    {
        $this->assertTrue(printer_usb_presente_da_evidenza(
            array(),
            array('direct usb://Zebra/ZD230 "Zebra"'),
            'usb://Zebra/ZD230'
        ));
        $this->assertFalse(printer_usb_presente_da_evidenza(array(), array(), 'usb://Zebra/ZD230'));
        $this->assertFalse(printer_usb_presente_da_evidenza(
            array(),
            array('direct socket://192.168.1.50 "rete"'),
            'usb://Zebra/ZD230'
        ));
    }

    public function testRamiReteSoloPartiPure(): void
    {
        // Validazione IP: nessun socket, passa anche senza rete/CUPS.
        $this->assertFalse(printer_ping(''));
        $this->assertFalse(printer_ping('non-un-ip'));
        $this->assertFalse(printer_ping('999.999.999.999'));
        // DIRETTA = coda locale, nessun ping.
        $this->assertTrue(printer_is_reachable('GX420t', 'DIRETTA'));
        $this->assertFalse(printer_is_reachable('ZD230', 'RETE', 'non-un-ip'));
        // Identita' nome->coda (nessuna mappa, come da F4.2).
        $this->assertSame('GX420t', printer_cups_queue('GX420t'));
        $this->assertSame('ZD230', printer_cups_queue('ZD230'));
        // Default CUPS da righe inline.
        $this->assertSame('GX420t', printer_lpstat_parse_default(array(
            'scheduler is running',
            'system default destination: GX420t',
        )));
        $this->assertSame('', printer_lpstat_parse_default(array('scheduler is running')));
    }
}
