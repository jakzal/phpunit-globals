<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;

class AnnotationExtension implements BeforeTestHook, AfterTestHook
{
    public function executeBeforeTest(string $test): void
    {
        GlobalsContainer::getInstance()->backupGlobals();
        AnnotationReader::getInstance()->readGlobalAnnotations($test);
    }

    public function executeAfterTest(string $test, float $time): void
    {
        GlobalsContainer::getInstance()->restoreGlobals();
    }
}
