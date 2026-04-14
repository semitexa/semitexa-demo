<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DeferredBlocksDemoResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/deferred',
    methods: ['GET'],
    responseWith: DeferredBlocksDemoResource::class,
    produces: ['application/json', 'text/html'],
)]
class DeferredBlocksPayload
{
}
