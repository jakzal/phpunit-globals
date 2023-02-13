<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Putenv
{
    use TargetAware;

    public function __construct(
        public readonly string $name,
        public readonly string $value = '',
        public readonly bool $unset = false,
    ) {
    }
}
