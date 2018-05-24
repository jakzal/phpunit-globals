<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use Zalas\PHPUnit\Globals\AnnotationListener;

/**
 * @env APP_ENV=test
 * @server APP_DEBUG=0
 */
class AnnotationListenerTest extends TestCase
{
    public function test_it_is_a_test_listener()
    {
        $this->assertInstanceOf(TestListener::class, new AnnotationListener());
    }

    /**
     * @env APP_ENV=test_foo
     * @server APP_DEBUG=1
     */
    public function test_it_reads_global_variables_from_method_annotations()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '1'], $_SERVER);
    }

    public function test_it_reads_global_variables_from_class_annotations()
    {
        $this->assertArraySubset(['APP_ENV' => 'test'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '0'], $_SERVER);
    }

    /**
     * @env FOO=foo
     * @server BAR=bar
     */
    public function test_it_reads_additional_global_variables_from_methods()
    {
        $this->assertArraySubset(['APP_ENV' => 'test'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '0'], $_SERVER);
        $this->assertArraySubset(['FOO' => 'foo'], $_ENV);
        $this->assertArraySubset(['BAR' => 'bar'], $_SERVER);
    }

    /**
     * @env APP_ENV=test_foo
     * @env APP_ENV=test_foo_bar
     * @server APP_DEBUG=1
     * @server APP_DEBUG=2
     */
    public function test_it_reads_the_latest_var_defined()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo_bar'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '2'], $_SERVER);
    }

    /**
     * @env APP_ENV
     * @server APP_DEBUG
     */
    public function test_it_reads_empty_vars()
    {
        $this->assertArraySubset(['APP_ENV' => ''], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => ''], $_SERVER);
    }

    public function test_it_backups_the_state()
    {
        // this test is only here so the next one could verify the state is brought back

        $_ENV['FOO'] = 'env_foo';
        $_SERVER['BAR'] = 'server_bar';

        $this->assertArrayHasKey('FOO', $_ENV);
        $this->assertArrayHasKey('BAR', $_SERVER);
    }

    /**
     * @depends test_it_backups_the_state
     */
    public function test_it_cleans_up_after_itself()
    {
        $this->assertArrayNotHasKey('FOO', $_ENV);
        $this->assertArrayNotHasKey('BAR', $_SERVER);
    }

    public function test_it_ignores_non_standard_test_cases()
    {
        $test = $this->prophesize(Test::class)->reveal();

        $listener = new AnnotationListener();

        // Our implementation only supports TestCase, not just any Test.
        $result = $listener->startTest($test);

        $this->assertEmpty($result);
    }
}
