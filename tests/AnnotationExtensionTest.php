<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use Zalas\PHPUnit\Globals\AnnotationExtension;

/**
 * @env APP_ENV=test
 * @server APP_DEBUG=0
 * @putenv APP_HOST=localhost
 */
class AnnotationExtensionTest extends TestCase
{
    public function test_it_is_a_test_hook()
    {
        $this->assertInstanceOf(BeforeTestHook::class, new AnnotationExtension());
        $this->assertInstanceOf(AfterTestHook::class, new AnnotationExtension());
    }

    /**
     * @env APP_ENV=test_foo
     * @server APP_DEBUG=1
     * @putenv APP_HOST=dev
     */
    public function test_it_reads_global_variables_from_method_annotations()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '1'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'dev'], \getenv());
    }

    public function test_it_reads_global_variables_from_class_annotations()
    {
        $this->assertArraySubset(['APP_ENV' => 'test'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '0'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'localhost'], \getenv());
    }

    /**
     * @env FOO=foo
     * @server BAR=bar
     * @putenv BAZ=baz
     */
    public function test_it_reads_additional_global_variables_from_methods()
    {
        $this->assertArraySubset(['APP_ENV' => 'test'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '0'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'localhost'], \getenv());
        $this->assertArraySubset(['FOO' => 'foo'], $_ENV);
        $this->assertArraySubset(['BAR' => 'bar'], $_SERVER);
        $this->assertArraySubset(['BAZ' => 'baz'], \getenv());
    }

    /**
     * @env APP_ENV=test_foo
     * @env APP_ENV=test_foo_bar
     * @server APP_DEBUG=1
     * @server APP_DEBUG=2
     * @putenv APP_HOST=host1
     * @putenv APP_HOST=host2
     */
    public function test_it_reads_the_latest_var_defined()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo_bar'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '2'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'host2'], \getenv());
    }

    /**
     * @env APP_ENV
     * @server APP_DEBUG
     * @putenv APP_HOST
     */
    public function test_it_reads_empty_vars()
    {
        $this->assertArrayNotHasKey('APP_ENV', $_ENV);
        $this->assertArrayNotHasKey('APP_DEBUG', $_SERVER);
        $this->assertArrayNotHasKey('APP_HOST', \getenv());
    }

    public function test_it_backups_the_state()
    {
        // this test is only here so the next one could verify the state is brought back

        $_ENV['FOO'] = 'env_foo';
        $_SERVER['BAR'] = 'server_bar';
        \putenv('FOO=putenv_foo');
        \putenv('USER=foobar');

        $this->assertArrayHasKey('FOO', $_ENV);
        $this->assertArrayHasKey('BAR', $_SERVER);
        $this->assertSame('putenv_foo', \getenv('FOO'));
        $this->assertSame('foobar', \getenv('USER'));
    }

    /**
     * @depends test_it_backups_the_state
     */
    public function test_it_cleans_up_after_itself()
    {
        $this->assertArrayNotHasKey('FOO', $_ENV);
        $this->assertArrayNotHasKey('BAR', $_SERVER);
        $this->assertFalse(\getenv('FOO'), 'It removes environment variables initialised in a test.');
        $this->assertNotSame('foobar', \getenv('USER'), 'It restores environment variables changed in a test.');
        $this->assertNotFalse(\getenv('USER'), 'It restores environment variables changed in a test.');
    }

    /**
     * Provides a replacement for the assertion deprecated in PHPUnit 8 and removed in PHPUnit 9.
     * @param array $subset
     * @param array $array
     */
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        self::assertSame($array, \array_replace_recursive($array, $subset));
    }
}
