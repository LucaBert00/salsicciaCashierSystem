<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Integration;

use PHPUnit\Framework\TestCase;

// Retry della Coda senza DB ne' registratore: il sender e' il callable
// iniettabile $trasmetti di Fiscale::ritentaCoda, i file stanno in una
// cartella temporanea. Separazione puro/trasporto intatta: nessun
// cambiamento al codice di produzione.
final class FiscaleCodaRetryTest extends TestCase
{
    private string $dir;
    private array $cfg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . '/fiscale_coda_' . getmypid() . '_' . uniqid();
        mkdir($this->dir, 0770, true);
        $this->cfg = [
            'queue_file' => $this->dir . '/coda.log',
            'sent_file' => $this->dir . '/inviati.log',
        ];
    }

    protected function tearDown(): void
    {
        foreach (['coda.log', 'inviati.log'] as $f) {
            if (is_file($this->dir . '/' . $f)) {
                unlink($this->dir . '/' . $f);
            }
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
        parent::tearDown();
    }

    private function accoda(string $xml, int $idOrdine): void
    {
        $riga = json_encode([
            'ts' => date('c'),
            'id_ordine' => $idOrdine,
            'metodo' => 'contanti',
            'motivo' => 'test',
            'xml' => $xml,
        ]);
        file_put_contents((string) $this->cfg['queue_file'], $riga . "\n", FILE_APPEND | LOCK_EX);
    }

    public function testRetryRecapitaESpostaInInviatiScartandoRigheCorrotte(): void
    {
        $this->accoda(\Salsiccia\Fiscale\Fiscale::buildXml([['iva' => 0.22, 'totale' => 10.0]], 'contanti'), 1);
        $this->accoda(\Salsiccia\Fiscale\Fiscale::buildXml([['iva' => 0.10, 'totale' => 5.0]], 'contanti'), 2);
        file_put_contents((string) $this->cfg['queue_file'], "riga-corrotta\n", FILE_APPEND | LOCK_EX);

        $esito = \Salsiccia\Fiscale\Fiscale::ritentaCoda($this->cfg, static fn (): array => ['ok' => true]);

        $this->assertSame(2, $esito['inviati']);
        $this->assertSame(1, $esito['scarti']);
        $this->assertSame(0, $esito['residui']);
        $this->assertSame('', (string) @file_get_contents((string) $this->cfg['queue_file']));
        $this->assertCount(2, file((string) $this->cfg['sent_file'], FILE_IGNORE_NEW_LINES));
    }

    public function testRetryLasciaInCodaQuandoIlSenderFallisceELimitaAMax(): void
    {
        $this->accoda(\Salsiccia\Fiscale\Fiscale::buildXml([['iva' => 0.22, 'totale' => 10.0]], 'contanti'), 1);
        $this->accoda(\Salsiccia\Fiscale\Fiscale::buildXml([['iva' => 0.22, 'totale' => 20.0]], 'contanti'), 2);

        $esito = \Salsiccia\Fiscale\Fiscale::ritentaCoda($this->cfg, static fn (): array => ['ok' => false], 1);

        $this->assertSame(0, $esito['inviati']);
        $this->assertSame(2, $esito['residui']);
        $this->assertCount(2, file((string) $this->cfg['queue_file'], FILE_IGNORE_NEW_LINES));
    }
}
