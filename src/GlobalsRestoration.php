<?php declare(strict_types=1);

namespace Zalas\PHPUnit\Globals;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class GlobalsRestoration implements FinishedSubscriber
{
    public function __construct(private readonly GlobalsBackup $globalsBackup)
    {
    }

    public function notify(Finished $event): void
    {
        $this->globalsBackup->restoreGlobals();
    }
}
