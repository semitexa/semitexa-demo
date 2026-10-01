<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response\Graphql;

use Semitexa\Core\Resource\Attribute\ResourceField;
use Semitexa\Core\Resource\Attribute\ResourceId;
use Semitexa\Core\Resource\Attribute\ResourceObject;
use Semitexa\Core\Resource\ResourceObjectInterface;

#[ResourceObject(type: 'demo.product.review')]
final readonly class ProductReviewGraphqlView implements ResourceObjectInterface
{
    public function __construct(
        #[ResourceId]
        public string $id,
        #[ResourceField(description: 'Id of the user who wrote the review.')]
        public string $author,
        #[ResourceField(description: 'Rating from 1 to 5.')]
        public int $rating,
        #[ResourceField(description: 'Review text.')]
        public string $headline,
    ) {}
}
