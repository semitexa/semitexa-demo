<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\DomainListener;

use Semitexa\Core\Attribute\AsEventListener;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Event\EventExecution;
use Semitexa\Demo\Application\Payload\Event\DemoExecutionShowcaseAsyncRequested;
use Semitexa\Demo\Application\Service\DemoExecutionShowcaseService;

#[AsEventListener(event: DemoExecutionShowcaseAsyncRequested::class, execution: EventExecution::Async)]
final class DemoExecutionShowcaseAsyncListener
{
    #[InjectAsReadonly]
    protected DemoExecutionShowcaseService $service;

    public function handle(DemoExecutionShowcaseAsyncRequested $event): void
    {
        $this->service->play($event->getRunId(), $event->getSessionId(), 'async', 'swoole defer');
    }
}
