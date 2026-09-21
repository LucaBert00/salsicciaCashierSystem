<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

// T30: statement finto gemello di FakeMysqli (superficie usata da db_exec()).
final class FakeStmt
{
    public string $tipi = '';
    public array $params = array();

    private bool $ok;

    public function __construct(bool $ok)
    {
        $this->ok = $ok;
    }

    // Nome snake imposto dalla superficie mysqli chiamata da db_exec().
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- firma mysqli
    public function bind_param(string $tipi, mixed ...$params): bool
    {
        $this->tipi = $tipi;
        $this->params = $params;
        return true;
    }

    public function execute(): bool
    {
        return $this->ok;
    }

    public function close(): void
    {
    }
}
