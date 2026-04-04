<?php

declare(strict_types=1);

namespace App\Application\Listener;

use App\Application\Event\DemoItemCreated;
use Semitexa\Core\Attributes\AsEventListener;
use Semitexa\Core\Event\EventExecution;

#[AsEventListener(event: DemoItemCreated::class, execution: EventExecution::Sync)]
final class DemoItemCreatedListener
{
    public function handle(DemoItemCreated $event): void
    {
        // Update in-request projections, metrics, or audit context immediately.
    }
}
