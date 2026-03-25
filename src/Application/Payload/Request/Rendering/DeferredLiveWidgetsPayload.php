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
    summary: 'Set refreshInterval and the block re-fetches its slot on a timer — live UI with zero JS.',
    order: 7,
    highlights: ['refreshInterval', 'auto-refresh', 'SSE reconnection', 'live counter'],
    entryLine: 'Set refreshInterval and the block re-fetches its slot on a timer — live UI with zero JS.',
    learnMoreLabel: 'See the refreshInterval config →',
    deepDiveLabel: 'SSE reconnection strategy →',
)]
class DeferredLiveWidgetsPayload
{
}
