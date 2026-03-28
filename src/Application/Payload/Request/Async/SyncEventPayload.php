<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/events/sync',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'events',
    title: 'Sync Events',
    slug: 'sync',
    summary: 'Dispatch an event and all sync listeners run before the response is sent.',
    order: 1,
    highlights: ['#[AsEvent]', '#[AsEventListener]', 'EventExecution::Sync', 'EventDispatcherInterface'],
    entryLine: 'Dispatch an event and all sync listeners run before the response is sent.',
    learnMoreLabel: 'See the event & listener code →',
    deepDiveLabel: 'Dispatcher execution modes →',
)]
class SyncEventPayload
{
    protected ?string $trigger = null;

    public function getTrigger(): ?string { return $this->trigger; }
    public function setTrigger(?string $trigger): void { $this->trigger = $trigger; }
}
