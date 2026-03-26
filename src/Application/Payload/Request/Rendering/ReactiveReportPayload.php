<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-report',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive Report',
    slug: 'reactive-report',
    summary: 'A cron job runs every 30s — the deferred block reflects live job state without page reload.',
    order: 9,
    highlights: ['refreshInterval', '#[AsScheduledJob]', 'DemoJobRun', 'Pending → Running → chart'],
    entryLine: 'A cron job runs every 30s — the deferred block reflects live job state without page reload.',
    learnMoreLabel: 'See cron config →',
    deepDiveLabel: 'LeaseHeartbeat & retry →',
)]
class ReactiveReportPayload
{
}
