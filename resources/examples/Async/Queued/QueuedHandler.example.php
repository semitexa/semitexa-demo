<?php

declare(strict_types=1);

namespace App\Application\Listener;

use App\Application\Event\DemoItemCreated;
use Semitexa\Core\Attribute\AsEventListener;
use Semitexa\Core\Event\EventExecution;

#[AsEventListener(
    event: DemoItemCreated::class,
    execution: EventExecution::Queued,
    queue: 'demo.notifications',
)]
final class QueuedHandler
{
    public function handle(DemoItemCreated $event): void
    {
        // Durable queue-backed processing with retries and dead-letter handling.
    }
}
