<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/di/overview',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'di',
    title: 'DI Canon',
    slug: 'overview',
    summary: 'One canonical DI path for container-managed classes: explicit properties, explicit lifecycles, deterministic boot.',
    order: 1,
    highlights: ['single-path DI', '#[InjectAsReadonly]', '#[InjectAsMutable]', 'boot-time validation'],
    entryLine: 'One canonical DI path for container-managed classes: explicit properties, explicit lifecycles, deterministic boot.',
    learnMoreLabel: 'See the Semitexa canon →',
    deepDiveLabel: 'Why mixed DI fails →',
)]
class DiOverviewPayload
{
}
