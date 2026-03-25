<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/deferred',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Deferred Blocks',
    slug: 'deferred',
    summary: 'Render the page shell instantly — deferred slots stream in over SSE as they complete.',
    order: 5,
    highlights: ['#[AsSlotResource(deferred: true)]', 'skeletonTemplate', 'SSE push', 'skeleton → content'],
    entryLine: 'Render the page shell instantly — deferred slots stream in over SSE as they complete.',
    learnMoreLabel: 'See all 6 deferred blocks →',
    deepDiveLabel: 'SSE push mechanism →',
)]
class DeferredBlocksPayload
{
}
