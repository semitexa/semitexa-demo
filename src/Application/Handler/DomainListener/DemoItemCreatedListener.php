<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\DomainListener;

use Semitexa\Core\Attributes\AsEventListener;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Event\EventDispatcherInterface;
use Semitexa\Core\Event\EventExecution;
use Semitexa\Demo\Application\Payload\Event\DemoItemCreated;
use Semitexa\Demo\Application\Payload\Event\DemoNotificationEvent;

#[AsEventListener(event: DemoItemCreated::class, execution: EventExecution::Sync)]
final class DemoItemCreatedListener
{
    #[InjectAsReadonly]
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function handle(DemoItemCreated $event): void
    {
        $notification = new DemoNotificationEvent();
        $notification->setMessage(
            sprintf('New item "%s" created in section "%s".', $event->getItemName(), $event->getSection())
        );
        $notification->setLevel('info');

        $this->eventDispatcher?->dispatch($notification);
    }
}
