<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class RestoreEnvironmentGlobalsSubscriber implements FinishedSubscriber
{
    public function __construct(
        private readonly Context $context,
    ) {
    }

    public function notify(Finished $event): void
    {
        $_SERVER = $this->context->getServer();
        $_ENV = $this->context->getEnv();

        foreach (\array_diff_assoc($this->context->getGetenv(), \getenv()) as $name => $value) {
            \putenv(\sprintf('%s=%s', $name, $value));
        }
        foreach (\array_diff_assoc(\getenv(), $this->context->getGetenv()) as $name => $value) {
            \putenv($name);
        }
    }
}
