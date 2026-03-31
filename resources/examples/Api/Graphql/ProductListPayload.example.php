<?php

declare(strict_types=1);

namespace App\Api\Product;

use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Graphql\Attributes\ExposeAsGraphql;

#[AsPayload(path: '/api/v1/products', methods: ['GET'], responseWith: ProductApiResponse::class)]
#[ExternalApi(version: 'v1')]
#[ExposeAsGraphql(
    field: 'products',
    rootType: 'query',
    output: ProductListGraphqlView::class,
)]
final class ProductListPayload
{
    protected int $page = 1;
    protected int $limit = 24;

    public function getPage(): int { return $this->page; }
    public function setPage(int|string|null $page): void { $this->page = max(1, (int) ($page ?? 1)); }

    public function getLimit(): int { return $this->limit; }
    public function setLimit(int|string|null $limit): void { $this->limit = min(100, max(1, (int) ($limit ?? 24))); }
}
