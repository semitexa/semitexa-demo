<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/rendering/deferred-live',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
)]
class DeferredLiveWidgetsPayload
{
}
