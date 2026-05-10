<?php

declare(strict_types=1);

namespace App\Payload\Routing;

use Semitexa\Core\Attribute\AsPublicPayload;
use App\Resource\CatalogPageResource;

#[AsPublicPayload(
    responseWith: CatalogPageResource::class,
    path: '/catalog',
    methods: ['GET'],
)]
final class PublicCatalogPayload
{
}
