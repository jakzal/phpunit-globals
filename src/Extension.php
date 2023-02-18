<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Runner\Extension\Extension as PhpunitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class Extension implements PhpunitExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $context = new Context();

        $facade->registerSubscriber(new PrepareTestEnvironmentSubscriber($context));
        $facade->registerSubscriber(new RestoreEnvironmentGlobalsSubscriber($context));
    }
}
