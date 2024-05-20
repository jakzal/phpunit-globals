<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AttributeExtensionWithDataProviderTest extends TestCase
{
    #[DataProvider('provider')]
    public function test_it_handles_dataproviders()
    {
        $this->assertTrue(true, 'It lets the test cases run normally');
    }

    public static function provider()
    {
        // This is just a dummy data provider
        yield [1];
    }
}
