<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\DomainListener;

use Semitexa\Core\Attributes\AsEventListener;
use Semitexa\Core\Event\EventExecution;
use Semitexa\Demo\Application\Payload\Event\DemoNotificationEvent;

#[AsEventListener(event: DemoNotificationEvent::class, execution: EventExecution::Async)]
final class DemoNotificationListener
{
    public function handle(DemoNotificationEvent $event): void
    {
        // In a real app: push to a notification queue, send email, write to audit log, etc.
        // Here the async execution itself is the demo point — this runs after the response is sent.
    }
}
