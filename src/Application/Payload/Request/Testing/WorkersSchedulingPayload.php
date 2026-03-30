<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/cli/workers-scheduling',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'cli',
    title: 'Workers & Scheduling',
    slug: 'workers-scheduling',
    summary: 'Run queues, scheduler pools, mail delivery, webhooks, and tenant-scoped commands from a coherent operator surface instead of bespoke daemons.',
    order: 6,
    highlights: ['queue:work', 'scheduler:list', 'scheduler:plan', 'scheduler:work', 'webhook:work', 'tenant:run'],
    entryLine: 'Semitexa is not only request-response code. The CLI also owns the long-running workers and operator interventions that keep the platform moving.',
    learnMoreLabel: 'See the worker topology →',
    deepDiveLabel: 'Operational patterns behind the commands →',
)]
final class WorkersSchedulingPayload
{
}
