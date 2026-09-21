<?php

declare(strict_types=1);

namespace Salsiccia\Tests\Unit;

// T30: manico DB finto per i service test (stessa superficie di mysqli usata da
// db_exec(): prepare/bind_param/execute/close). Niente DB, niente rete.
final class FakeMysqli
{
    public string $sql = '';
    public FakeStmt $stmt;

    private bool $ok;

    public function __construct(bool $ok)
    {
        $this->ok = $ok;
        $this->stmt = new FakeStmt($ok);
    }

    public function prepare(string $sql): object|false
    {
        $this->sql = $sql;
        return $this->ok ? $this->stmt : false;
    }
}
