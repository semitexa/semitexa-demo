<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/deferred-live',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Live Widgets',
    slug: 'deferred-live',
    summary: 'A live slot can refresh itself on a timer while the page stays SSR-first — no SPA runtime and no handwritten polling layer.',
    order: 8,
    highlights: ['refreshInterval', 'auto-refresh', 'SSE reconnection', 'live counter'],
    entryLine: 'Set refreshInterval and the server keeps re-rendering the widget for you. Live UI without converting the page into an app shell.',
    learnMoreLabel: 'See the live-slot contract →',
    deepDiveLabel: 'SSE reconnection strategy →',
)]
class DeferredLiveWidgetsPayload
{
}
