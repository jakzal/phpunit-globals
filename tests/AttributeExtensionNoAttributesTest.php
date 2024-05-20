<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Tests;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * A test case with no global variable attributes.
 */
class AttributeExtensionNoAttributesTest extends TestCase
{
    public function test_it_backups_the_state()
    {
        // this test is only here so the next one could verify the state is brought back

        $_ENV['FOO'] = 'env_foo';
        $_SERVER['BAR'] = 'server_bar';

        $this->assertArrayHasKey('FOO', $_ENV);
        $this->assertArrayHasKey('BAR', $_SERVER);
    }

    #[Depends('test_it_backups_the_state')]
    public function test_it_cleans_up_after_itself()
    {
        $this->assertArrayNotHasKey('FOO', $_ENV);
        $this->assertArrayNotHasKey('BAR', $_SERVER);
    }
}
