<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;
use Zalas\PHPUnit\Globals\AnnotationExtension;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zalas\PHPUnit\Globals\Attribute\Putenv;
use Zalas\PHPUnit\Globals\Attribute\Server;

#[Env('APP_ENV', 'test')]
#[Server('APP_DEBUG', '0')]
#[Putenv('APP_HOST', 'localhost')]
class AttributeExtensionTest extends TestCase
{
    public function test_it_is_a_test_hook()
    {
        $this->assertInstanceOf(BeforeTestHook::class, new AnnotationExtension());
        $this->assertInstanceOf(AfterTestHook::class, new AnnotationExtension());
    }

    #[Env('APP_ENV', 'test_foo')]
    #[Server('APP_DEBUG', '1')]
    #[Putenv('APP_HOST', 'dev')]
    public function test_it_reads_global_variables_from_method_attributes()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '1'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'dev'], \getenv());
    }

    public function test_it_reads_global_variables_from_class_attributes()
    {
        $this->assertArraySubset(['APP_ENV' => 'test'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '0'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'localhost'], \getenv());
    }

    #[Env('FOO', 'foo')]
    #[Server('BAR', 'bar')]
    #[Putenv('BAZ', 'baz')]
    public function test_it_reads_additional_global_variables_from_methods()
    {
        $this->assertArraySubset(['APP_ENV' => 'test'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '0'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'localhost'], \getenv());
        $this->assertArraySubset(['FOO' => 'foo'], $_ENV);
        $this->assertArraySubset(['BAR' => 'bar'], $_SERVER);
        $this->assertArraySubset(['BAZ' => 'baz'], \getenv());
    }

    #[Env('APP_ENV', 'test_foo')]
    #[Env('APP_ENV', 'test_foo_bar')]
    #[Server('APP_DEBUG', '1')]
    #[Server('APP_DEBUG', '2')]
    #[PutEnv('APP_HOST', 'host1')]
    #[PutEnv('APP_HOST', 'host2')]
    public function test_it_reads_the_latest_var_defined()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo_bar'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '2'], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => 'host2'], \getenv());
    }

    #[Env('APP_ENV')]
    #[Server('APP_DEBUG')]
    #[PutEnv('APP_HOST')]
    public function test_it_reads_empty_vars()
    {
        $this->assertArraySubset(['APP_ENV' => ''], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => ''], $_SERVER);
        $this->assertArraySubset(['APP_HOST' => ''], \getenv());
    }

    #[Env('APP_ENV', unset: true)]
    #[Server('APP_DEBUG', unset: true)]
    #[PutEnv('APP_HOST', unset: true)]
    public function test_it_unsets_vars()
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
     * @depends self::test_it_backups_the_state
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
