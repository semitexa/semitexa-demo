<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/events/queued',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
)]
class QueuedHandlerPayload
{
}
