<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Graphql\Output;

final readonly class ProductReviewGraphqlView
{
    public function __construct(
        public string $author,
        public int $rating,
        public string $headline,
    ) {}
}
