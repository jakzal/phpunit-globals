<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests\Stub;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use Zalas\PHPUnit\Globals\AnnotationReader;
use Zalas\PHPUnit\Globals\AttributeReader;
use Zalas\PHPUnit\Globals\GlobalsContainer;

final class CombinedExtension implements BeforeTestHook, AfterTestHook
{

    public function executeBeforeTest(string $test): void
    {
        GlobalsContainer::getInstance()->backupGlobals();

        AnnotationReader::getInstance()->readGlobalAnnotations($test);
        AttributeReader::getInstance()->readGlobalAttributes($test);
    }

    public function executeAfterTest(string $test, float $time): void
    {
        GlobalsContainer::getInstance()->restoreGlobals();
    }
}
