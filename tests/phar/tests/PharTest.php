<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Zalas\PHPUnit\Globals\Attribute\Env;
use Zalas\PHPUnit\Globals\Attribute\Server;

class PharTest extends TestCase
{
    #[Env("APP_ENV", "test_foo")]
    #[Server("APP_DEBUG", "1")]
    public function test_it_reads_global_variables_from_method_attributes()
    {
        $this->assertArraySubset(['APP_ENV' => 'test_foo'], $_ENV);
        $this->assertArraySubset(['APP_DEBUG' => '1'], $_SERVER);
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
