<?php

declare(strict_types=1);

namespace App\Payload\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use App\Resource\CatalogPageResource;

#[PublicEndpoint]
#[AsPayload(
    responseWith: CatalogPageResource::class,
    path: '/catalog',
    methods: ['GET'],
)]
final class PublicCatalogPayload
{
}
