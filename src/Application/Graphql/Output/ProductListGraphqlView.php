<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Graphql\Output;

final readonly class ProductListGraphqlView
{
    /**
     * @param list<ProductGraphqlView> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
    ) {}
}
