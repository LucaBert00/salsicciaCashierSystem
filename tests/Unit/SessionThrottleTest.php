<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Salsiccia\Support\Session;
use Salsiccia\Support\Throttle;

// T28: throttle server-side in env.inc (file, fuori docroot).
// Niente DB, niente rete: ogni test usa un file temp dedicato.
final class SessionThrottleTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = (string)tempnam(sys_get_temp_dir(), 't28_');
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            @unlink($this->file);
        }
        parent::tearDown();
    }

    public function testCinqueFailBloccanoSessantaSecondi(): void
    {
        for ($i = 0; $i < 4; $i++) {
            Throttle::fail('t', 'ip', $this->file);
            $this->assertFalse(Throttle::throttled('t', 'ip', $this->file));
        }
        Throttle::fail('t', 'ip', $this->file);
        $this->assertTrue(Throttle::throttled('t', 'ip', $this->file));
    }

    public function testBloccoSopravviveAlResetSessioneESiSbloccaConOk(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Throttle::fail('t', 'ip', $this->file);
        }
        $_SESSION = array(); // attaccante butta il cookie: il file blocca comunque
        $this->assertTrue(Throttle::throttled('t', 'ip', $this->file));
        Throttle::ok('t', 'ip', $this->file);
        $this->assertFalse(Throttle::throttled('t', 'ip', $this->file));
    }

    public function testCookieParamsInduriti(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['REQUEST_SCHEME']);
        $_SERVER['SERVER_PORT'] = 80;
        $this->assertTrue(Session::start());
        $p = session_get_cookie_params();
        $this->assertTrue(!empty($p['httponly']));
        $this->assertSame('Lax', $p['samesite'] ?? '');
        session_write_close();
    }
}
