<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

final class Context
{
    public function __construct(
        private array $server = [],
        private array $env = [],
        private array|string|false $getenv = [],
    ) {
    }

    public function backupGlobals(): void
    {
        $this->server = $_SERVER;
        $this->env = $_ENV;
        $this->getenv = \getenv();
    }

    public function getServer(): array
    {
        return $this->server;
    }

    public function getEnv(): array
    {
        return $this->env;
    }

    public function getGetenv(): bool|array|string
    {
        return $this->getenv;
    }
}
