<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;

final class AttributeExtension implements BeforeTestHook, AfterTestHook
{
    public function executeBeforeTest(string $test): void
    {
        GlobalsContainer::getInstance()->backupGlobals();
        AttributeReader::getInstance()->readGlobalAttributes($test);
    }

    public function executeAfterTest(string $test, float $time): void
    {
        GlobalsContainer::getInstance()->restoreGlobals();
    }
}
