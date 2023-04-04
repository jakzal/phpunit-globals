<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Env
{
    use TargetAware;

    public function __construct(
        public string $name,
        public string $value = '',
        public bool $unset = false,
    ) {
    }
}
