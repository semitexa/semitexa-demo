<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/events/queued',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'events',
    title: 'Queued Handler',
    slug: 'queued',
    summary: 'Events survive restarts and scale across workers — backed by a durable message queue.',
    order: 3,
    highlights: ['EventExecution::Queued', 'queue transport', 'RabbitMQ', 'retry', 'DLQ'],
    entryLine: 'Events survive restarts and scale across workers — backed by a durable message queue.',
    learnMoreLabel: 'See the queue configuration →',
    deepDiveLabel: 'Queue driver internals →',
)]
class QueuedHandlerPayload
{
}
