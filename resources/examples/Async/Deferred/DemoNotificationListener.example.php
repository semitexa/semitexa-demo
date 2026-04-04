<?php

declare(strict_types=1);

namespace App\Application\Listener;

use App\Application\Event\DemoItemCreated;
use Semitexa\Core\Attributes\AsEventListener;
use Semitexa\Core\Event\EventExecution;

#[AsEventListener(event: DemoItemCreated::class, execution: EventExecution::Async)]
final class DemoNotificationListener
{
    public function handle(DemoItemCreated $event): void
    {
        // Send mail, write audit output, or refresh projections after the response.
    }
}
