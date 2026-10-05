<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

use PHPUnit\Framework\TestCase;

// F4.1 #98: i globali csrf_* serviti via autoload.files (helpers.php) tengono
// token stabile, guard POST+CSRF e hidden field (T14 invariati). Niente DB.
final class SupportHelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['tok'] = bin2hex(random_bytes(32));
    }

    public function testTokenStabileEdEsadecimale(): void
    {
        $prima = csrf_token();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $prima);
        $this->assertSame($prima, csrf_token());
    }

    public function testOkFalsoSenzaPost(): void
    {
        $postBackup = $_POST;
        $_POST = array();
        try {
            $this->assertFalse(csrf_ok());
        } finally {
            $_POST = $postBackup;
        }
    }

    public function testOkVeroConTokCorrispondente(): void
    {
        $postBackup = $_POST;
        $_POST['tok'] = csrf_token();
        try {
            $this->assertTrue(csrf_ok());
        } finally {
            $_POST = $postBackup;
        }
    }

    public function testFieldEmetteHiddenConToken(): void
    {
        ob_start();
        csrf_field();
        $html = (string)ob_get_clean();

        $this->assertStringContainsString('name="tok"', $html);
        $this->assertStringContainsString(csrf_token(), $html);
    }
}
