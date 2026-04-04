<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DeferredBlocksDemoResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/deferred',
    methods: ['GET'],
    responseWith: DeferredBlocksDemoResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Deferred Blocks',
    slug: 'deferred',
    summary: 'SSR renders the shell first, then expensive regions stream in as real HTML over SSE — no SPA handoff and no client-side page rebuild.',
    order: 3,
    highlights: ['#[AsSlotResource(deferred: true)]', 'skeletonTemplate', 'SSE push', 'skeleton → content'],
    entryLine: 'The page is usable immediately, and slow regions arrive later as server-rendered HTML instead of hydration-heavy client code.',
    learnMoreLabel: 'See the SSR-first flow →',
    deepDiveLabel: 'How deferred delivery works →',
    opensInNewTab: true,
)]
class DeferredBlocksPayload
{
}
