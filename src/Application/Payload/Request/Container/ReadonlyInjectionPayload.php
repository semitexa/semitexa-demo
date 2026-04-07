<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/di/readonly',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'di',
    title: 'Readonly Injection',
    slug: 'readonly',
    summary: 'One explicit DI path, one shared worker instance — fast at runtime and stable under reload.',
    order: 2,
    highlights: ['#[InjectAsReadonly]', 'worker-scoped', 'single-path DI', 'reload-stable'],
    entryLine: 'One explicit DI path, one shared worker instance — fast at runtime and stable under reload.',
    learnMoreLabel: 'See the injection attribute →',
    deepDiveLabel: 'Container tiers explained →',
)]
class ReadonlyInjectionPayload
{
}
