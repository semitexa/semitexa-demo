<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-analytics',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive Analytics',
    slug: 'reactive-analytics',
    summary: 'Three cron jobs write snapshots — three panels fill in independently as each job completes.',
    order: 11,
    highlights: ['multi-job', 'DemoAnalyticsSnapshot', 'refreshInterval: 5', 'panel orchestration'],
    entryLine: 'Three cron jobs write snapshots — three panels fill in independently as each job completes.',
    learnMoreLabel: 'See panel config →',
    deepDiveLabel: 'Multi-job orchestration →',
)]
class ReactiveAnalyticsPayload
{
}
