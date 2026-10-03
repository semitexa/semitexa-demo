<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response\Graphql;

use Semitexa\Core\Resource\Attribute\ResourceField;
use Semitexa\Core\Resource\Attribute\ResourceId;
use Semitexa\Core\Resource\Attribute\ResourceObject;
use Semitexa\Core\Resource\ResourceObjectInterface;

#[ResourceObject(type: 'demo.product.category')]
final readonly class ProductCategoryGraphqlView implements ResourceObjectInterface
{
    public function __construct(
        #[ResourceId]
        public string $slug,
        #[ResourceField(description: 'Category display name.')]
        public string $name,
    ) {}
}
