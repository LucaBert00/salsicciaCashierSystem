<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Salsiccia\Support\Session;

// MAINT-02 #128: golden byte-identico dei 5 frammenti su dataset noto, PRIMA
// del refactor. Congela il comportamento vecchio; dopo lo split lo stesso
// test deve restare verde senza modifiche alle attese.
// Dataset: cassa 7 con ordine aperto 10 (tipo pre, 2 righe), standby 20;
// cassa 99 senza nulla (rami vuoti). Token CSRF fisso per output stabile.
// Processo separato: functionsFrontend.inc non e caricabile insieme agli
// stub eval di CassaViewTest nello stesso processo.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- classe singola, attributo di isolamento
#[RunTestsInSeparateProcesses]
final class Maint02GoldenTest extends TestCase
{
    private const TOK = 'TOK128';

    private const DB = 'salsiccia_maint02_golden';

    private ?\mysqli $db = null;

    private array $sessionBackup = [];

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../functionsFrontend.inc';
        Session::start();
        $this->sessionBackup = $_SESSION ?? [];
        $_SESSION = array('id_cassa' => 7, 'tok' => self::TOK);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        try {
            $admin = new \mysqli('127.0.0.1', 'root', '', 'mysql');
        } catch (\Throwable) {
            $this->markTestSkipped('MariaDB non raggiungibile per il golden MAINT-02');
        }
        if ($admin->connect_error) {
            $this->markTestSkipped('MariaDB non raggiungibile per il golden MAINT-02');
        }
        $admin->query('DROP DATABASE IF EXISTS `' . self::DB . '`');
        $admin->query('CREATE DATABASE `' . self::DB . '` CHARACTER SET utf8mb4');
        $admin->close();
        $db = new \mysqli('127.0.0.1', 'root', '', self::DB);
        if ($db->connect_error) {
            $this->markTestSkipped('MariaDB non raggiungibile per il golden MAINT-02');
        }
        $this->db = $db;
        $this->seedPieno();
    }

    protected function tearDown(): void
    {
        if ($this->db instanceof \mysqli) {
            $this->db->close();
        }
        try {
            $admin = new \mysqli('127.0.0.1', 'root', '', 'mysql');
            if (!$admin->connect_error) {
                $admin->query('DROP DATABASE IF EXISTS `' . self::DB . '`');
                $admin->close();
            }
        } catch (\Throwable) {
            // Pulizia best-effort.
        }
        $_SESSION = is_array($this->sessionBackup) ? $this->sessionBackup : [];
        parent::tearDown();
    }

    private function seedPieno(): void
    {
        $db = $this->db;
        $db->query('CREATE TABLE ordini (id_ordine INT UNSIGNED NOT NULL AUTO_INCREMENT, data_ora DATETIME DEFAULT NULL, tipo VARCHAR(3) DEFAULT NULL, totale FLOAT DEFAULT NULL, n_pezzi INT UNSIGNED DEFAULT NULL, id_cassa TINYINT UNSIGNED DEFAULT NULL, chiuso CHAR(1) DEFAULT NULL, num_biglietti INT NOT NULL, PRIMARY KEY (id_ordine)) ENGINE=InnoDB');
        $db->query("CREATE TABLE prodotti (id_prodotto INT UNSIGNED NOT NULL AUTO_INCREMENT, descrizione_prod VARCHAR(100) DEFAULT NULL, prezzo FLOAT DEFAULT NULL, iva FLOAT NOT NULL, testo_biglietto VARCHAR(100) DEFAULT NULL, olpp CHAR(1) NOT NULL DEFAULT 'F', barcode VARCHAR(20) NOT NULL, PRIMARY KEY (id_prodotto)) ENGINE=InnoDB");
        $db->query('CREATE TABLE righe_ordini (id_ordine INT UNSIGNED NOT NULL, id_prodotto INT UNSIGNED NOT NULL, quantita INT UNSIGNED DEFAULT NULL, totale FLOAT DEFAULT NULL, PRIMARY KEY (id_ordine,id_prodotto)) ENGINE=InnoDB');
        $db->query("INSERT INTO prodotti (descrizione_prod, prezzo, iva, barcode) VALUES ('PANINO SALSICCIA', 3.50, 0.10, '111'), ('BIRRA', 2.00, 0.22, '222')");
        $db->query("INSERT INTO ordini (id_ordine, data_ora, tipo, totale, n_pezzi, id_cassa, chiuso, num_biglietti) VALUES (10, '2026-10-01 12:00:00', 'pre', 9.00, 3, 7, '0', 0), (20, '2026-10-01 10:00:00', 'nor', 5.00, 2, 7, 'S', 0)");
        $db->query('INSERT INTO righe_ordini (id_ordine, id_prodotto, quantita, totale) VALUES (10, 1, 2, 7.00), (10, 2, 1, 2.00)');
    }

    private function html(callable $fn): string
    {
        ob_start();
        $fn();
        return (string) ob_get_clean();
    }

    public function testIndicatoreTipoPienoEVuoto(): void
    {
        $db = $this->db;
        $_SESSION['id_cassa'] = 7;
        $pieno = $this->html(fn() => \mostraIndicatoreTipo($db));
        $this->assertSame('<span style="color:#e54b3c;font-weight:800;font-size:18px;margin-left:auto;">P</span>', $pieno);

        $_SESSION['id_cassa'] = 99;
        $this->assertSame('', $this->html(fn() => \mostraIndicatoreTipo($db)));
    }

    public function testBoxRiepilogoPienoEVuoto(): void
    {
        $db = $this->db;
        $_SESSION['id_cassa'] = 7;
        $pieno = $this->html(fn() => \mostraBoxRiepilogo($db));
        $this->assertSame("<div class=\"order-number\">ORDINE #10</div>\r\n              <div class=\"order-total\">\r\n                <h3>TOTALE DA PAGARE</h3>\r\n                <h1 class=\"item-cost\">&euro; 9,00</h1>\r\n              </div>", $pieno);
        $this->assertStringContainsString('ORDINE #', $pieno);
        $this->assertStringContainsString('TOTALE DA PAGARE', $pieno);

        $_SESSION['id_cassa'] = 99;
        $vuoto = $this->html(fn() => \mostraBoxRiepilogo($db));
        $this->assertSame("<div class=\"order-number\">NESSUN ORDINE APERTO</div>\r\n              <div class=\"order-total\">\r\n                <h3>TOTALE DA PAGARE</h3>\r\n                <h1 class=\"item-cost\">&euro; 0,00</h1>\r\n              </div>", $vuoto);
        $this->assertStringContainsString('NESSUN ORDINE APERTO', $vuoto);
    }

    public function testListaProdottiMultiSingolaEVuota(): void
    {
        $db = $this->db;
        $_SESSION['id_cassa'] = 7;
        $multi = $this->html(fn() => \mostraListaProdotti($db));
        $this->assertSame("<li>\r\n                    <div class=\"cart-item\">\r\n                        <span class=\"item-qty\">2</span>\r\n                        <span class=\"item-desc\">PANINO SALSICCIA</span>\r\n                        <span class=\"item-cost\">&euro; 7,00</span>\r\n                    </div>\r\n                  </li><li>\r\n                    <div class=\"cart-item\">\r\n                        <span class=\"item-qty\">1</span>\r\n                        <span class=\"item-desc\">BIRRA</span>\r\n                        <span class=\"item-cost\">&euro; 2,00</span>\r\n                    </div>\r\n                  </li>", $multi);

        $db->query('DELETE FROM righe_ordini WHERE id_ordine=10 AND id_prodotto=2');
        $singola = $this->html(fn() => \mostraListaProdotti($db));
        $this->assertSame("<li>\r\n                    <div class=\"cart-item\">\r\n                        <span class=\"item-qty\">2</span>\r\n                        <span class=\"item-desc\">PANINO SALSICCIA</span>\r\n                        <span class=\"item-cost\">&euro; 7,00</span>\r\n                    </div>\r\n                  </li>", $singola);

        $_SESSION['id_cassa'] = 99;
        $vuota = $this->html(fn() => \mostraListaProdotti($db));
        $this->assertSame('<li><div class="cart-item"><span class="item-desc">Nessun prodotto nell\'ordine</span></div></li>', $vuota);
        $this->assertStringContainsString('Nessun prodotto', $vuota);
    }

    public function testModificaOrdineMultiSingolaEVuota(): void
    {
        $db = $this->db;
        $_SESSION['id_cassa'] = 7;
        $multi = $this->html(fn() => \mostraModificaOrdine($db));
        $this->assertStringContainsString('class="modifica-table"', $multi);
        $this->assertStringContainsString('?action=mq&id=1&qta=1', $multi);
        $this->assertStringContainsString('?action=mq&id=1&qta=3', $multi);
        $this->assertStringContainsString('?action=mq&id=2&qta=2', $multi);
        $this->assertStringContainsString('?action=mr&id=1', $multi);
        $this->assertStringContainsString('?action=mr&id=2', $multi);
        $this->assertStringContainsString('action-btn action-delete">✕<', $multi);
        $this->assertStringContainsString('value="TOK128"', $multi);
        $this->assertStringContainsString('>2<', $multi);

        $db->query('DELETE FROM righe_ordini WHERE id_ordine=10 AND id_prodotto=2');
        $singola = $this->html(fn() => \mostraModificaOrdine($db));
        $this->assertStringContainsString('class="modifica-table"', $singola);
        $this->assertStringNotContainsString('BIRRA', $singola);
        $this->assertStringContainsString('PANINO SALSICCIA', $singola);

        $_SESSION['id_cassa'] = 99;
        $vuota = $this->html(fn() => \mostraModificaOrdine($db));
        $this->assertSame('<section class="modifica-section"><p style="text-align:center;margin-top:40px;font-size:18px;color:#8c9ba5;">Nessun prodotto nell\'ordine</p></section>', $vuota);
    }

    public function testOrdiniStandbyPieniEVuoti(): void
    {
        $db = $this->db;
        $_SESSION['id_cassa'] = 7;
        $pieni = $this->html(fn() => \mostraOrdiniStandby($db));
        $this->assertStringContainsString('<table class="standby-table">', $pieni);
        $this->assertStringContainsString('<td>20</td>', $pieni);
        $this->assertStringContainsString('<td>01/10/2026 10:00:00</td>', $pieni);
        $this->assertStringContainsString('5,00&euro;', $pieni);
        $this->assertStringContainsString('?action=ra&id=20', $pieni);
        $this->assertStringContainsString('standby-attiva-btn', $pieni);
        $this->assertStringContainsString('value="TOK128"', $pieni);

        $db->query("DELETE FROM ordini WHERE chiuso='S'");
        $vuoti = $this->html(fn() => \mostraOrdiniStandby($db));
        $this->assertSame('<section class="standby-section"><p class="standby-empty">Nessun ordine in standby</p></section>', $vuoti);
    }
}
