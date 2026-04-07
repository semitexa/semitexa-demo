<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-report',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive Report',
    slug: 'reactive-report',
    summary: 'Background work updates an SSR-first slot in place, so the UI feels live without falling back to SPA state orchestration.',
    order: 10,
    highlights: ['refreshInterval', '#[AsScheduledJob]', 'DemoJobRun', 'Pending → Running → chart'],
    entryLine: 'A scheduled job changes server state, and the slot keeps reflecting that state live with no page reload and no client-side state machine.',
    learnMoreLabel: 'See the live-report flow →',
    deepDiveLabel: 'LeaseHeartbeat & retry →',
)]
class ReactiveReportPayload
{
}
