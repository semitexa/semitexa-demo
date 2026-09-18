<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
    path: '/demo/routing/public-endpoint',
    methods: ['GET'],
)]
class PublicEndpointPayload
{
}
