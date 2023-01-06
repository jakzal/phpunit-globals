<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class AnnotationExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $globalsBackup = new GlobalsBackup();
        $globalsAnnotationReader = new GlobalsAnnotationReader();
        $globalsRestoration = new GlobalsRestoration($globalsBackup);
        $facade->registerSubscribers($globalsBackup, $globalsAnnotationReader, $globalsRestoration);
    }
}
