<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use App\Api\Graphql\Output\ProductMetricsGraphqlView;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Graphql\Attributes\ExposeAsGraphql;

#[AsPayload(
    path: '/__graphql/products/metrics',
    methods: ['POST'],
    responseWith: ResourceResponse::class,
)]
#[ExposeAsGraphql(
    field: 'productMetrics',
    rootType: 'query',
    output: ProductMetricsGraphqlView::class,
)]
final class ProductMetricsPayload
{
    protected string $status = 'active';

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = trim($status); }
}
