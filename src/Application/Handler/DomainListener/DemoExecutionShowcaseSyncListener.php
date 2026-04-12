<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\DomainListener;

use Semitexa\Core\Attribute\AsEventListener;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Event\EventExecution;
use Semitexa\Demo\Application\Payload\Event\DemoExecutionShowcaseSyncRequested;
use Semitexa\Demo\Application\Service\DemoExecutionShowcaseService;

#[AsEventListener(event: DemoExecutionShowcaseSyncRequested::class, execution: EventExecution::Sync)]
final class DemoExecutionShowcaseSyncListener
{
    #[InjectAsReadonly]
    protected DemoExecutionShowcaseService $service;

    public function handle(DemoExecutionShowcaseSyncRequested $event): void
    {
        $this->service->play($event->getRunId(), $event->getSessionId(), 'sync', 'sync listener');
    }
}
