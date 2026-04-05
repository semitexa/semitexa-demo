<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ProductListV0Resource;
use Semitexa\Api\Attribute\ApiVersion;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Core\Attribute\AsPayload;

#[ExternalApi]
#[ApiVersion('0.9', deprecatedSince: '2026-01-01', sunsetDate: '2026-12-01')]
#[AsPayload(
    path: '/api/v0/products',
    methods: ['GET'],
    responseWith: ProductListV0Resource::class,
    produces: ['application/json'],
)]
final class ProductListV0Payload
{
}
