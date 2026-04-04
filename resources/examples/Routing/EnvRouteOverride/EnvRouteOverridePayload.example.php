<?php

declare(strict_types=1);

namespace App\Application\Payload\Routing;

use App\Application\Resource\Page\CatalogLandingResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: 'env::CATALOG_ROUTE_PATH::/catalog',
    methods: ['GET'],
    responseWith: CatalogLandingResource::class,
    produces: ['text/html'],
)]
final class EnvRouteOverridePayload
{
}
