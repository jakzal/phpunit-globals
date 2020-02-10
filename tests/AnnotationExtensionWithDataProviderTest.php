<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\TestCase;

class AnnotationExtensionWithDataProviderTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function test_it_handles_dataproviders() {
        $this->assertTrue(true, 'It lets the test cases run normally');
    }

    public function provider() {
        // This is just a dummy data provider
        yield [1];
    }
}
