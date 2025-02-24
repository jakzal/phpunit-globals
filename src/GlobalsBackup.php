<?php declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;

final class GlobalsBackup implements PreparationStartedSubscriber
{
    private array $server;
    private array $env;
    private array $getenv;

    public function notify(PreparationStarted $event): void
    {
        $this->server = $_SERVER;
        $this->env = $_ENV;
        $this->getenv = \getenv();
    }

    public function restoreGlobals(): void
    {
        $_SERVER = $this->server;
        $_ENV = $this->env;

        foreach (\array_diff_assoc($this->getenv, \getenv()) as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }
        foreach (\array_diff_assoc(\getenv(), $this->getenv) as $name => $value) {
            \putenv($name);
        }
    }
}
