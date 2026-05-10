<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
    path: 'env::DEMO_BASIC_ROUTE_PATH::/demo/routing/basic',
    methods: ['GET'],
)]
class BasicRoutePayload
{
}
