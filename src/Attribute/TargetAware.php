<?php
declare(strict_types=1);

namespace Zalas\PHPUnit\Globals\Attribute;

trait TargetAware
{
    private int $target = 0;

    /**
     * @internal
     */
    public function withTarget(int $target): self
    {
        $clone = clone $this;
        $clone->target = $target;

        return $clone;
    }

    /**
     * @internal
     */
    public function getTarget(): int
    {
        return $this->target;
    }
}
