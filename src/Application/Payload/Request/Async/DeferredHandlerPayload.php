<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/events/deferred',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'events',
    title: 'Deferred Handler',
    slug: 'deferred',
    summary: 'Heavy work runs after the response is sent — the user gets instant feedback.',
    order: 2,
    highlights: ['EventExecution::Async', 'Swoole::Event::defer()', 'post-response', 'non-blocking'],
    entryLine: 'Heavy work runs after the response is sent — the user gets instant feedback.',
    learnMoreLabel: 'See the deferred listener →',
    deepDiveLabel: 'How Swoole defer works →',
)]
class DeferredHandlerPayload
{
}
