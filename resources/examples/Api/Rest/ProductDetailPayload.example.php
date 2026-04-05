<?php

declare(strict_types=1);

namespace App\Api\Product;

use App\Api\Product\ProductApiResponse;
use Semitexa\Api\Attribute\ApiVersion;
use Semitexa\Api\Attribute\ExternalApi;
use Semitexa\Core\Attribute\AsPayload;

#[AsPayload(path: '/api/v1/products/{slug}', methods: ['GET'], responseWith: ProductApiResponse::class)]
#[ExternalApi(version: 'v1')]
#[ApiVersion(version: '1.0.0')]
final class ProductDetailPayload
{
    protected string $slug = '';

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = trim($slug); }
}
