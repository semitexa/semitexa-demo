<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    responseWith: DemoFeatureResource::class,
    path: '/demo/di/readonly',
    methods: ['GET'],
)]
#[DemoFeature(
    section: 'di',
    title: 'Readonly Injection',
    slug: 'readonly',
    summary: 'Stateless services share one instance per worker — zero-cost injection after boot.',
    order: 1,
    highlights: ['#[InjectAsReadonly]', 'worker-scoped', 'shared instance', 'zero allocation'],
    entryLine: 'Stateless services share one instance per worker — zero-cost injection after boot.',
    learnMoreLabel: 'See the injection attribute →',
    deepDiveLabel: 'Container tiers explained →',
)]
class ReadonlyInjectionPayload
{
}
