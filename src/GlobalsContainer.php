<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

final class GlobalsContainer
{
    use SingletonTrait;

    private function __construct()
    {
    }

    public function backupGlobals()
    {
        $this->server = $_SERVER;
        $this->env = $_ENV;
        $this->getenv = \getenv();
    }

    public function restoreGlobals()
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
