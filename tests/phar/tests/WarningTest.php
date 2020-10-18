<?php declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\TestCase;

class WarningTest extends TestCase
{
    /**
     * @dataProvider warning_producing_provider
     */
    public function test_it_handles_warnings()
    {
        $this->assertTrue(true, 'It lets the test cases run normally');
    }

    /**
     * This provider intentionally produces a PHPUnit warning by using the same
     * key twice
     */
    public function warning_producing_provider()
    {
        yield 'test' => [1];
        yield 'test' => [2];
    }
}
