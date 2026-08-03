<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response\Graphql;

final readonly class ProductCategoryGraphqlView
{
    public function __construct(
        public string $slug,
        public string $name,
    ) {}
}
