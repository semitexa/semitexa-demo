<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ProductListResource;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Authorization\Attribute\AsServicePayload;

#[ExternalApi(version: 'v1')]
#[AsServicePayload(
    path: '/api/products',
    methods: ['GET'],
    responseWith: ProductListResource::class,
    produces: ['application/json'],
)]
final class ProductListPayload
{
}
