<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests\Stub;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Zalas\PHPUnit\Globals\GlobalsAnnotationReader;
use Zalas\PHPUnit\Globals\GlobalsAttributeReader;
use Zalas\PHPUnit\Globals\GlobalsBackup;
use Zalas\PHPUnit\Globals\GlobalsRestoration;

final class CombinedExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $globalsBackup = new GlobalsBackup();
        $globalsAttributeReader = new GlobalsAttributeReader();
        $globalsAnnotationReader = new GlobalsAnnotationReader();
        $globalsRestoration = new GlobalsRestoration($globalsBackup);
        $facade->registerSubscribers(
            $globalsBackup,
            $globalsAttributeReader,
            $globalsAnnotationReader,
            $globalsRestoration
        );
    }
}
