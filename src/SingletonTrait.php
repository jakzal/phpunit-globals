<?php

declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

trait SingletonTrait
{
    private static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
