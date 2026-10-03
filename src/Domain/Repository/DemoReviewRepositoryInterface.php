<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoReview;

interface DemoReviewRepositoryInterface
{
    public function findById(string $id): ?DemoReview;

    public function save(DemoReview $entity): DemoReview;

    /** @return list<DemoReview> */
    public function findAll(int $limit = 100): array;

    /** @return list<DemoReview> */
    public function findByProduct(string $productId): array;

    /**
     * Reviews of several products in one query, newest first per product.
     *
     * @param list<string> $productIds
     * @return array<string, list<DemoReview>> keyed by product id; a product without reviews is absent
     */
    public function findByProducts(array $productIds): array;

    /** @return list<DemoReview> */
    public function findByUser(string $userId): array;

    /** @return list<DemoReview> */
    public function findByRating(int $rating): array;
}
