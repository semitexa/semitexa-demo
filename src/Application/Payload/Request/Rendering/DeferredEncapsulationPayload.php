<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/deferred-encapsulation',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Block Isolation',
    slug: 'deferred-encapsulation',
    summary: 'Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.',
    order: 9,
    highlights: ['DOM scoping', 'data-instance', 'block isolation', 'independent timers'],
    entryLine: 'Two identical blocks on the same page run independently — scoped DOM, scoped JS, no conflicts.',
    learnMoreLabel: 'See the isolation pattern →',
    deepDiveLabel: 'DOM scoping mechanism →',
)]
class DeferredEncapsulationPayload
{
}
