<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ProductSchemaResource;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Core\Attribute\AsPublicPayload;

#[ExternalApi]
#[AsPublicPayload(
    path: '/api/schema/products',
    methods: ['GET'],
    responseWith: ProductSchemaResource::class,
    produces: ['application/schema+json'],
)]
final class ProductSchemaPayload
{
}
