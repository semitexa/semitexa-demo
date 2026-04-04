<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/events/sse',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'events',
    title: 'SSE Stream',
    slug: 'sse',
    summary: 'Real-time server push without WebSockets — connect once and receive real backend events over plain HTTP.',
    order: 4,
    highlights: ['SseEndpointHandler', 'AsyncResourceSseServer', 'EventSource', 'text/event-stream'],
    entryLine: 'This demo connects to the backend SSE runtime and receives live named events without polling or a page reload.',
    learnMoreLabel: 'See the SSE handler →',
    deepDiveLabel: 'SSE connection lifecycle →',
)]
class SseStreamPayload
{
}
