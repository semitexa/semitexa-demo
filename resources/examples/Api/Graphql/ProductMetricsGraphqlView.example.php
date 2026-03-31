<?php

declare(strict_types=1);

namespace App\Api\Graphql\Output;

final readonly class ProductMetricsGraphqlView
{
    public function __construct(
        public int $total,
        public int $active,
        public int $archived,
        public float $averagePrice,
    ) {}
}
