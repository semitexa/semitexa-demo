<?php

declare(strict_types=1);

namespace App\Api\Graphql\Output;

final readonly class ProductGraphqlView
{
    /**
     * @param list<ProductReviewGraphqlView> $reviews
     */
    public function __construct(
        public string $slug,
        public string $name,
        public float $price,
        public ?string $description,
        public string $status,
        public ?ProductCategoryGraphqlView $category = null,
        public array $reviews = [],
    ) {}
}
