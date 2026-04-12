<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/events/arena',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'events',
    title: 'Execution Arena',
    slug: 'arena',
    summary: 'Launch the same backend intent in sync, Swoole async, and queued modes, then watch the proof arrive over SSE.',
    order: 0,
    highlights: ['EventExecution::Sync', 'EventExecution::Async', 'EventExecution::Queued', 'SSE proof stream'],
    entryLine: 'One click launches one backend intent. The arena shows which work blocked the response, which work escaped into async execution, and which work waited for a queue worker.',
    learnMoreLabel: 'See the execution arena code →',
    deepDiveLabel: 'Why this proves the async model →',
)]
class ExecutionArenaPayload
{
}
