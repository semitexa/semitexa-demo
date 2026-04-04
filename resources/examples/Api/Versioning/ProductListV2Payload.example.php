<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ProductListV2Resource;
use Semitexa\Api\Attributes\ApiVersion;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Core\Attribute\AsPayload;

#[ExternalApi]
#[ApiVersion('2.0')]
#[AsPayload(
    path: '/api/v2/products',
    methods: ['GET'],
    responseWith: ProductListV2Resource::class,
    produces: ['application/json'],
)]
final class ProductListV2Payload
{
}
