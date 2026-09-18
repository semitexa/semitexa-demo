<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DeferredBlocksDemoResource;

#[AsPublicPayload(
    path: '/demo/rendering/deferred',
    methods: ['GET'],
    responseWith: DeferredBlocksDemoResource::class,
    produces: ['text/html', 'application/json'],
)]
class DeferredBlocksPayload
{
}
