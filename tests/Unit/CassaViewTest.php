<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Cassa\CassaView;

// F1.3 #85: smoke di CassaView::render() via output buffering.
// functionsFrontend.inc NON e richiedibile in isolamento (require dbConnect
// con die + sessione + gestisciAzioni a top-level, v. PowerF2Test): le
// mostra*() reali restano fuori portata, quindi qui stanno doppi minimi che
// registrano la chiamata via marker. La vista (shell, titoli, match, tinta)
// e quella vera; i corpi mostra* restano intoccati per scope F1.3.
// DB: doppio db_select esistente come gli altri test (nessun nuovo
// framework); get_result falso = riga categoria assente -> hex di default,
// senza mai toccare mysqli_fetch_array procedurale.
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi e stub in un solo file di test
final class CassaViewFakeStmt
{
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function bind_param(string $tipi, mixed ...$params): bool
    {
        return true;
    }

    public function execute(): bool
    {
        return true;
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function get_result(): false
    {
        return false;
    }

    public function close(): void
    {
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi e stub in un solo file di test
final class CassaViewFakeMysqli
{
    public string $sql = '';

    public function prepare(string $sql): object|false
    {
        $this->sql = $sql;
        return new CassaViewFakeStmt();
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- doppi e stub in un solo file di test
final class CassaViewTest extends TestCase
{
    private array $getBackup = [];

    private mixed $sessionBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getBackup = $_GET;
        $this->sessionBackup = $_SESSION ?? [];
        $_GET = [];
        $_SESSION = [];
        foreach (array(
            'mostraNavCategorie', 'mostraIndicatoreTipo', 'mostraTitolo',
            'mostraBarraBarcode', 'mostraTabellaProdotti', 'mostraFooterBottoni',
            'mostraPannelloAdmin', 'mostraSchermataStampa', 'mostraModificaOrdine',
            'mostraFooterModifica', 'mostraOpzioni', 'mostraOrdiniStandby',
            'mostraRipristinaDb', 'mostraContatori', 'mostraPrintReset',
            'mostraRestart', 'mostraShutdown', 'mostraSwitchPrinter',
            'mostraModificaInfo', 'mostraBoxRiepilogo', 'mostraAzioniLaterali',
            'mostraListaProdotti', 'mostraPannelloConfig', 'mostraAzioniExtra',
        ) as $fn) {
            if (function_exists($fn)) {
                continue;
            }
            eval('function ' . $fn . '(...$args): void { echo "<!--stub:' . $fn . '-->"; }');
        }
        // Stessa riga T19 della vista: la funzione reale, estratta dal
        // sorgente come in PowerF2Test (mai modifiche al sorgente per il test).
        if (!function_exists('coloreCategoriaHex')) {
            $src = (string) file_get_contents(__DIR__ . '/../../functionsFrontend.inc');
            $m = array();
            if (!preg_match('/^function coloreCategoriaHex\b.*?\r?\n\}/ms', $src, $m)) {
                $this->fail('funzione coloreCategoriaHex non trovata in functionsFrontend.inc');
            }
            eval($m[0] . "\n");
        }
        // F2.4 #91: AdminView riusa as-is isAdmin()/cassaCorrente()/
        // isFieraAttiva()/ID_CASSA (functionsFrontend.inc/set.inc, mai caricati
        // qui); doppi minimi globali come le mostra* sopra, mai logica vera.
        if (!function_exists('isAdmin')) {
            eval('function isAdmin(): bool { return !empty($_SESSION["admin"]); }');
        }
        if (!function_exists('cassaCorrente')) {
            eval('function cassaCorrente(): int { $c = (int)($_SESSION["id_cassa"] ?? 1); return ($c >= 1 && $c <= 999) ? $c : 1; }');
        }
        if (!function_exists('isFieraAttiva')) {
            eval('function isFieraAttiva(): bool { return false; }');
        }
        if (!defined('ID_CASSA')) {
            define('ID_CASSA', 1);
        }
    }

    protected function tearDown(): void
    {
        $_GET = $this->getBackup;
        $_SESSION = is_array($this->sessionBackup) ? $this->sessionBackup : [];
        parent::tearDown();
    }

    private function html(string $view, $db, int $cat = 19): string
    {
        ob_start();
        CassaView::render($view, $db, $cat);
        return (string) ob_get_clean();
    }

    public function testCassaContieneTintaDaCatalogRepo(): void
    {
        $db = new CassaViewFakeMysqli();

        $out = $this->html('cassa', $db);

        // Tinta in shell + riga categoria assente -> default odierno index.php.
        $this->assertStringContainsString('--hex-cat:', $out);
        $this->assertStringContainsString('--hex-cat:#e7e9eb', $out);
        // La query tinta vive nella vista (CatalogRepo::categoria), non altrove.
        $this->assertStringContainsString('categorie', $db->sql);
        // Shell aside sempre visibile + renderer giusti invocati.
        $this->assertStringContainsString('RIEPILOGO ORDINE', $out);
        $this->assertStringContainsString('<!--stub:mostraTabellaProdotti-->', $out);
        $this->assertStringContainsString('<!--stub:mostraListaProdotti-->', $out);
    }

    public function testRepairSenzaTintaNeQuery(): void
    {
        // F2.4 #91: corpoRepair via AdminView::repair() (stessi byte form,
        // difesa isAdmin dentro); serve sessione admin come in produzione.
        $_SESSION['admin'] = true;
        $db = new CassaViewFakeMysqli();

        $out = $this->html('repair', $db);

        $this->assertStringContainsString('RIPRISTINA DB', $out);
        $this->assertStringContainsString('ATTENZIONE', $out);
        $this->assertStringContainsString('REPAIR DATABASE', $out);
        // Mai tinta su admin: niente style, niente query categoria.
        $this->assertStringNotContainsString('--hex-cat', $out);
        $this->assertSame('', $db->sql);
        $this->assertStringNotContainsString('INSERISCI IL CODICE', $out);
    }

    public function testAzioneNonValida(): void
    {
        $out = $this->html('azione-non-valida', new CassaViewFakeMysqli());

        $this->assertStringContainsString('AZIONE NON VALIDA', $out);
    }

    public function testGetManipolatoNonCambiaOutput(): void
    {
        $pulito = $this->html('cassa', new CassaViewFakeMysqli());
        $_GET = array('action' => 'repair', 'cat' => 999, 'ok' => 1, 'id' => 7);
        $sporcato = $this->html('cassa', new CassaViewFakeMysqli());

        $this->assertSame($pulito, $sporcato);

        // F2.4 #91: repair via AdminView legge $_GET['page'] come oggi;
        // action/cat/ok/id restano ignorati, page no (paginazione invariata).
        $_SESSION['admin'] = true;
        $pulitoRepair = $this->html('repair', new CassaViewFakeMysqli());
        $_GET = array('action' => 'cassa', 'cat' => 1);
        $this->assertSame($pulitoRepair, $this->html('repair', new CassaViewFakeMysqli()));
    }

    public function testModificaEConfigLogin(): void
    {
        $out = $this->html('modifica', new CassaViewFakeMysqli());

        $this->assertStringContainsString('MODIFICA ORDINE', $out);
        $this->assertStringContainsString('<!--stub:mostraModificaOrdine-->', $out);

        // Login non admin: corpo cassa con tinta + tastierino in aside.
        // F2.4 #91: tastierino via AdminView::tastierino() (stessi byte, stesso JS).
        $db = new CassaViewFakeMysqli();
        $login = $this->html('config', $db);

        $this->assertStringContainsString('--hex-cat:', $login);
        $this->assertStringContainsString('INSERISCI IL CODICE:', $login);
        $this->assertStringContainsString('form-admin-code', $login);
        $this->assertStringContainsString('categorie', $db->sql);
    }

    public function testColoreCategoriaHexRealeNonDefault(): void
    {
        // La mappa colori esiste e '01' non cade nel fallback bottone default.
        $this->assertNotSame('#FFFFFF', \coloreCategoriaHex('01'));
    }
}
