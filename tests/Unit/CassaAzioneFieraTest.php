<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Config\CassaFlags;
use Salsiccia\Support\Storage;

// F7.1 #113: cassa_azione_fiera allineato al contratto $post_ok degli altri 10
// (punto 24). Quattro proprieta: GET non muta e non termina, POST senza token
// non muta, POST+CSRF+val muta con normalizzazione === "1" ? "1" : "0",
// non-admin non muta mai (guard isAdmin primo, difesa in profondita: la riga
// fiera in routes/cassa.php ha auth null). L'handler fa header()+exit sul solo
// POST riuscito (redirect PRG) e sul guard non-admin: quelle invocazioni
// avvengono nel processo figlio, il padre asserisce su file dei flag + marker
// di ritorno senza mai subire exit. Scrittura reale in
// storage/cassa_flags.json (gitignored) con backup/ripristino come casa;
// nessun override di path in Storage, nessuna modifica a helpers.php.
final class CassaAzioneFieraTest extends TestCase
{
    private string $file = '';

    private bool $avevaFile = false;

    private string $backup = '';

    /** @var array<string,mixed> */
    private array $getBackup = [];

    /** @var array<string,mixed> */
    private array $postBackup = [];

    /** @var array<string,mixed> */
    private array $serverBackup = [];

    private mixed $sessionBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = Storage::path('cassa_flags.json');
        $this->avevaFile = is_readable($this->file);
        $this->backup = $this->avevaFile ? (string) @file_get_contents($this->file) : '';
        $this->getBackup = $_GET;
        $this->postBackup = $_POST;
        $this->serverBackup = $_SERVER;
        $this->sessionBackup = $_SESSION ?? [];
    }

    protected function tearDown(): void
    {
        if ($this->avevaFile) {
            @file_put_contents($this->file, $this->backup, LOCK_EX);
        } else {
            @unlink($this->file);
        }
        $_GET = $this->getBackup;
        $_POST = $this->postBackup;
        $_SERVER = $this->serverBackup;
        $_SESSION = is_array($this->sessionBackup) ? $this->sessionBackup : [];
        parent::tearDown();
    }

    private function rawFlag(): string|false
    {
        clearstatcache(true, $this->file);
        if (!is_readable($this->file)) {
            return false;
        }
        return @file_get_contents($this->file);
    }

    /**
     * Esegue l'handler reale nel processo figlio e restituisce [output, exit].
     * "FIERA-RETURNED" in output = l'handler e ritornato (nessun exit).
     *
     * @param array<string,mixed> $session
     * @param array<string,mixed> $post
     * @return array{0:string,1:int}
     */
    private function eseguiFiera(array $session, array $post, string $method): array
    {
        $root = dirname(__DIR__, 2);
        $base = (string) tempnam(sys_get_temp_dir(), 'fiera_');
        @unlink($base);
        $script = $base . '.php';
        $code = '<?php' . "\n"
            . 'declare(strict_types=1);' . "\n"
            . 'require ' . var_export($root . '/vendor/autoload.php', true) . ';' . "\n"
            . 'if (session_status() === PHP_SESSION_NONE) { session_start(); }' . "\n"
            . '$_SESSION = ' . var_export($session, true) . ';' . "\n"
            . '$_POST = ' . var_export($post, true) . ';' . "\n"
            . '$_GET = array(\'action\' => \'fiera\');' . "\n"
            . '$_SERVER[\'REQUEST_METHOD\'] = ' . var_export($method, true) . ';' . "\n"
            . 'require ' . var_export($root . '/functionsFrontend.inc', true) . ';' . "\n"
            . 'cassa_azione_fiera(null, null);' . "\n"
            . 'echo "FIERA-RETURNED";' . "\n";
        file_put_contents($script, $code);
        $out = [];
        $exit = 0;
        exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1', $out, $exit);
        @unlink($script);
        return [implode("\n", $out), (int) $exit];
    }

    public function testGetNonMutaENonTermina(): void
    {
        CassaFlags::cassaImpostaFiera('0');
        $prima = $this->rawFlag();

        [$out, $exit] = $this->eseguiFiera(['admin' => true, 'tok' => 't1'], [], 'GET');

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('FIERA-RETURNED', $out);
        $this->assertSame($prima, $this->rawFlag());
    }

    public function testPostSenzaTokenNonMuta(): void
    {
        CassaFlags::cassaImpostaFiera('0');
        $prima = $this->rawFlag();

        [$out, $exit] = $this->eseguiFiera(['admin' => true, 'tok' => 't1'], ['val' => '1'], 'POST');

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('FIERA-RETURNED', $out);
        $this->assertSame($prima, $this->rawFlag());
    }

    public function testPostConTokenMutaConNormalizzazione(): void
    {
        CassaFlags::cassaImpostaFiera('0');
        [$out1, $exit1] = $this->eseguiFiera(['admin' => true, 'tok' => 't1'], ['tok' => 't1', 'val' => '1'], 'POST');
        $this->assertSame(0, $exit1);
        $this->assertStringNotContainsString('FIERA-RETURNED', $out1);
        $this->assertSame('1', $this->leggiModalita());

        CassaFlags::cassaImpostaFiera('1');
        [$out2, $exit2] = $this->eseguiFiera(['admin' => true, 'tok' => 't1'], ['tok' => 't1', 'val' => '0'], 'POST');
        $this->assertSame(0, $exit2);
        $this->assertStringNotContainsString('FIERA-RETURNED', $out2);
        $this->assertSame('0', $this->leggiModalita());

        CassaFlags::cassaImpostaFiera('1');
        [$out3, $exit3] = $this->eseguiFiera(
            ['admin' => true, 'tok' => 't1'],
            ['tok' => 't1', 'val' => 'qualunque'],
            'POST'
        );
        $this->assertSame(0, $exit3);
        $this->assertStringNotContainsString('FIERA-RETURNED', $out3);
        $this->assertSame('0', $this->leggiModalita());
    }

    public function testNonAdminNonMutaMai(): void
    {
        CassaFlags::cassaImpostaFiera('1');
        $prima = $this->rawFlag();

        [$out, $exit] = $this->eseguiFiera(['tok' => 't1'], ['tok' => 't1', 'val' => '0'], 'POST');

        $this->assertSame(0, $exit);
        $this->assertStringNotContainsString('FIERA-RETURNED', $out);
        $this->assertSame($prima, $this->rawFlag());
    }

    private function leggiModalita(): ?string
    {
        clearstatcache(true, $this->file);
        if (!is_readable($this->file)) {
            return null;
        }
        $raw = @file_get_contents($this->file);
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $j = json_decode($raw, true);
        if (!is_array($j) || !isset($j['modalita_fiera'])) {
            return null;
        }
        return (string) $j['modalita_fiera'];
    }
}
