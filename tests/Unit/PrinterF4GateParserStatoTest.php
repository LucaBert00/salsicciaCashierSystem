<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Printer\CupsState;
use Salsiccia\Printer\PrinterRegistry;

// F4.6 #56: gate/parser/stato F4 con fixture lpstat inline, mai CUPS/rete.
// Solo funzioni pure di env.inc (F4.1/F4.2); nessun exec, socket o storage reale.
final class PrinterF4GateParserStatoTest extends TestCase
{
    public function testGateNomiFinaliAmmessi(): void
    {
        $this->assertTrue(PrinterRegistry::gateAllowed('ZD230', 'DIRETTA', 'ZPL'));
        $this->assertTrue(PrinterRegistry::gateAllowed('ZD230', 'RETE', 'ZPL'));
        $this->assertTrue(PrinterRegistry::gateAllowed('GX420t', 'DIRETTA', 'ZPL'));
        $this->assertTrue(PrinterRegistry::gateAllowed('GX420t', 'DIRETTA', 'EPL'));
        $this->assertTrue(PrinterRegistry::gateAllowed('TLP2844', 'DIRETTA', 'EPL'));
        $this->assertTrue(PrinterRegistry::gateAllowed('LP2844', 'DIRETTA', 'EPL'));
    }

    public function testGateRifiutiInclusiVecchiZebra(): void
    {
        foreach (array('Zebra_Multi', 'Zebra_Net', 'Zebra_GX420t', 'Zebra_EPL_1', 'Zebra_EPL_2', 'Zebra_Qualsiasi') as $vecchio) {
            $this->assertFalse(PrinterRegistry::gateAllowed($vecchio, 'DIRETTA', 'ZPL'), $vecchio);
            $this->assertFalse(PrinterRegistry::isContinuous($vecchio, 'DIRETTA', 'ZPL'), $vecchio);
        }
        $this->assertFalse(PrinterRegistry::gateAllowed('GX420t', 'RETE', 'ZPL'));
        $this->assertFalse(PrinterRegistry::gateAllowed('ZD230', 'DIRETTA', 'EPL'));
        $this->assertFalse(PrinterRegistry::gateAllowed('TLP2844', 'RETE', 'EPL'));
        $this->assertFalse(PrinterRegistry::gateAllowed('TLP2844', 'DIRETTA', 'ZPL'));
        $this->assertFalse(PrinterRegistry::gateAllowed('', 'DIRETTA', 'ZPL'));
        $this->assertTrue(PrinterRegistry::isContinuous('ZD230', 'RETE', 'ZPL'));
        $this->assertTrue(PrinterRegistry::isContinuous('GX420t', 'DIRETTA', 'EPL'));
        $this->assertFalse(PrinterRegistry::isContinuous('TLP2844', 'DIRETTA', 'EPL'));
        $this->assertFalse(PrinterRegistry::isContinuous('LP2844', 'DIRETTA', 'EPL'));
    }

    public function testParserIdleEPrintingConDeviceAcceptingJobs(): void
    {
        $idle = CupsState::lpstatParseStato('GX420t', array(
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

        $prn = CupsState::lpstatParseStato('ZD230', array(
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
        $dis = CupsState::lpstatParseStato('TLP2844', array(
            'device for TLP2844: usb://Zebra/TLP2844?serial=X',
            'TLP2844 not accepting requests since Wed Oct 29 12:30:00 2025',
            'printer TLP2844 disabled since Wed Oct 29 12:30:00 2025 - Paused',
        ), '');
        $this->assertSame('disabled', $dis['state']);
        $this->assertFalse((bool) $dis['enabled']);
        $this->assertFalse((bool) $dis['accepting']);
        $this->assertSame('Paused', $dis['reason']);

        $unk = CupsState::lpstatParseStato('LP2844', array('printer LP2844 unknown'), '');
        $this->assertSame('unknown', $unk['state']);
        $this->assertFalse((bool) $unk['enabled']);
        $this->assertNotSame('', trim((string) $unk['reason']));

        $assente = CupsState::lpstatParseStato('GX420t', array('scheduler is running'), '');
        $this->assertSame('unknown', $assente['state']);
        $this->assertNotSame('', trim((string) $assente['reason']));
    }

    public function testFusioneConfermaNeutraTieneBase(): void
    {
        $base = CupsState::lpstatParseStato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $base['verificabile'] = true;

        $idle = CupsState::lpstatParseStato('GX420t', array(
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $prn = CupsState::lpstatParseStato('GX420t', array(
            'printer GX420t now printing GX420t-42.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $this->assertSame($base, CupsState::lpstatApplicaConfermaP($base, $idle));
        $this->assertSame($base, CupsState::lpstatApplicaConfermaP($base, $prn));
        $this->assertSame($base, CupsState::lpstatApplicaConfermaP($base, null));
        $this->assertSame($base, CupsState::lpstatApplicaConfermaP($base, array()));
    }

    public function testFusioneDisabledUnknownVinceRosso(): void
    {
        $base = CupsState::lpstatParseStato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $base['verificabile'] = true;

        $dis = CupsState::lpstatParseStato('GX420t', array(
            'printer GX420t disabled since Wed Oct 29 12:30:00 2025 - Paused',
        ), '');
        $rosso = CupsState::lpstatApplicaConfermaP($base, $dis);
        $this->assertSame('disabled', $rosso['state']);
        $this->assertFalse((bool) $rosso['enabled']);
        $this->assertSame('Paused', $rosso['reason']);
        $this->assertFalse(CupsState::statoLocaleOk($rosso, true));

        $assente = CupsState::lpstatParseStato('GX420t', array(), '');
        $rosso2 = CupsState::lpstatApplicaConfermaP($base, $assente);
        $this->assertFalse(CupsState::statoLocaleOk($rosso2, true));
        $this->assertNotSame('', trim((string) $rosso2['reason']));
    }

    public function testStatoLocaleOkIdleEPrinting(): void
    {
        $idle = CupsState::lpstatParseStato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $idle['verificabile'] = true;
        $this->assertTrue(CupsState::statoLocaleOk($idle, true));

        $prn = CupsState::lpstatParseStato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'GX420t-42 Zebra 1024 Wed Oct 29 12:31:00 2025',
            'printer GX420t now printing GX420t-42.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $prn['verificabile'] = true;
        $this->assertTrue(CupsState::statoLocaleOk($prn, true));
    }

    public function testStatoLocaleKoDeviceUsb(): void
    {
        $base = CupsState::lpstatParseStato('GX420t', array(
            'device for GX420t: usb://Zebra Technologies/ZTC GX420t?serial=31J115301224',
            'GX420t accepting requests since Wed Oct 29 12:27:19 2025',
            'printer GX420t is idle.  enabled since Wed Oct 29 12:27:19 2025',
        ), '');
        $base['verificabile'] = true;

        $noDev = $base;
        $noDev['device'] = '';
        $this->assertFalse(CupsState::statoLocaleOk($noDev, true));

        $nullDev = $base;
        $nullDev['device'] = 'file:///dev/null';
        $this->assertFalse(CupsState::statoLocaleOk($nullDev, true));

        $this->assertFalse(CupsState::statoLocaleOk($base, false));

        $noVer = $base;
        $noVer['verificabile'] = false;
        $this->assertFalse(CupsState::statoLocaleOk($noVer, true));

        $noAcc = $base;
        $noAcc['accepting'] = false;
        $this->assertFalse(CupsState::statoLocaleOk($noAcc, true));
    }

    public function testUsbPresenzaSeriale(): void
    {
        $dev = 'usb://Zebra Technologies/ZTC GX420t?serial=31J115301224';
        $this->assertTrue(CupsState::usbPresenteDaEvidenza(
            array(),
            array('direct usb://Zebra Technologies/ZTC GX420t?serial=31J115301224 "Zebra"'),
            $dev
        ));
        $this->assertFalse(CupsState::usbPresenteDaEvidenza(
            array(),
            array('direct usb://Zebra Technologies/ZTC GX420t?serial=ALTRO123 "Zebra"'),
            $dev
        ));
    }

    public function testUsbPresenzaGenericaEAssenza(): void
    {
        $this->assertTrue(CupsState::usbPresenteDaEvidenza(
            array(),
            array('direct usb://Zebra/ZD230 "Zebra"'),
            'usb://Zebra/ZD230'
        ));
        $this->assertFalse(CupsState::usbPresenteDaEvidenza(array(), array(), 'usb://Zebra/ZD230'));
        $this->assertFalse(CupsState::usbPresenteDaEvidenza(
            array(),
            array('direct socket://192.168.1.50 "rete"'),
            'usb://Zebra/ZD230'
        ));
    }

    public function testRamiReteSoloPartiPure(): void
    {
        // Validazione IP: nessun socket, passa anche senza rete/CUPS.
        $this->assertFalse(CupsState::ping(''));
        $this->assertFalse(CupsState::ping('non-un-ip'));
        $this->assertFalse(CupsState::ping('999.999.999.999'));
        // DIRETTA = coda locale, nessun ping.
        $this->assertTrue(CupsState::isReachable('GX420t', 'DIRETTA'));
        $this->assertFalse(CupsState::isReachable('ZD230', 'RETE', 'non-un-ip'));
        // Identita' nome->coda (nessuna mappa, come da F4.2).
        $this->assertSame('GX420t', CupsState::cupsQueue('GX420t'));
        $this->assertSame('ZD230', CupsState::cupsQueue('ZD230'));
        // Default CUPS da righe inline.
        $this->assertSame('GX420t', CupsState::lpstatParseDefault(array(
            'scheduler is running',
            'system default destination: GX420t',
        )));
        $this->assertSame('', CupsState::lpstatParseDefault(array('scheduler is running')));
    }
}
