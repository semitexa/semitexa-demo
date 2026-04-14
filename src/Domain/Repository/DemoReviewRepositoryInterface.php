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

    /** @return list<DemoReview> */
    public function findByUser(string $userId): array;

    /** @return list<DemoReview> */
    public function findByRating(int $rating): array;
}
