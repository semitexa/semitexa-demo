<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response\Graphql;

use Semitexa\Core\Resource\Attribute\ResourceField;
use Semitexa\Core\Resource\Attribute\ResourceListOf;
use Semitexa\Core\Resource\Attribute\ResourceObject;
use Semitexa\Core\Resource\ResourceObjectInterface;

#[ResourceObject(type: 'demo.product.list')]
final readonly class ProductListGraphqlView implements ResourceObjectInterface
{
    /**
     * @param list<ProductGraphqlView> $items
     */
    public function __construct(
        #[ResourceListOf(ProductGraphqlView::class, description: 'The products on this page.')]
        public array $items,
        #[ResourceField(description: 'Products matching the filters, across all pages.')]
        public int $total,
        #[ResourceField(description: 'Page number, starting at 1.')]
        public int $page,
        #[ResourceField(description: 'Page size, 1 to 24.')]
        public int $limit,
    ) {}
}
