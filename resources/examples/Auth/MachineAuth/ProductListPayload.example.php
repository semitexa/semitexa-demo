<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ProductListResource;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Core\Attributes\AsPayload;

#[ExternalApi(version: 'v1')]
#[AsPayload(
    path: '/api/products',
    methods: ['GET'],
    responseWith: ProductListResource::class,
    produces: ['application/json'],
)]
final class ProductListPayload
{
}
