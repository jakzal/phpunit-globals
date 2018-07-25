<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PharTest extends TestCase
{
    /**
     * @env APP_ENV=test_foo
     * @server APP_DEBUG=1
     */
    public function test_it_reads_global_variables_from_method_annotations()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '1'], $_SERVER);
    }
}
