<?php

declare(strict_types=1);

namespace App\Api\Product;

use App\Api\Product\ProductApiResponse;
use App\Api\Product\ProductGraphqlView;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Graphql\Attributes\ExposeAsGraphql;

#[AsPayload(path: '/api/v1/products/{slug}', methods: ['GET'], responseWith: ProductApiResponse::class)]
#[ExternalApi(version: 'v1')]
#[ExposeAsGraphql(
    field: 'productBySlug',
    rootType: 'query',
    output: ProductGraphqlView::class,
)]
final class ProductDetailPayload
{
    protected string $slug = '';

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = trim($slug); }
}
