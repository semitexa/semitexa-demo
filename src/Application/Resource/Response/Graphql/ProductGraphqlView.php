<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response\Graphql;

use Semitexa\Core\Resource\Attribute\ResourceField;
use Semitexa\Core\Resource\Attribute\ResourceId;
use Semitexa\Core\Resource\Attribute\ResourceListOf;
use Semitexa\Core\Resource\Attribute\ResourceObject;
use Semitexa\Core\Resource\ResourceObjectInterface;

/**
 * GraphQL output of one demo product. A #[ResourceObject], so the schema derives
 * nested `category` and `reviews` from this contract: a plain output DTO only
 * ever exposes its scalar properties.
 */
#[ResourceObject(type: 'demo.product')]
final readonly class ProductGraphqlView implements ResourceObjectInterface
{
    /**
     * @param list<ProductReviewGraphqlView> $reviews
     */
    public function __construct(
        #[ResourceId]
        public string $slug,
        #[ResourceField(description: 'Product name.')]
        public string $name,
        #[ResourceField(description: 'Price in USD.')]
        public float $price,
        #[ResourceField(description: 'Product description.')]
        public ?string $description,
        #[ResourceField(description: 'Catalogue status, e.g. active.')]
        public string $status,
        #[ResourceField(description: 'Category the product belongs to.')]
        public ?ProductCategoryGraphqlView $category = null,
        #[ResourceListOf(ProductReviewGraphqlView::class, description: 'Up to four reviews, as the REST detail shows.')]
        public array $reviews = [],
    ) {}
}
