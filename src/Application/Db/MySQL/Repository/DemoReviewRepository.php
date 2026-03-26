<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Orm\Repository\AbstractRepository;

#[AsService]
final class DemoReviewRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoReviewResource::class;
    }

    public function findByProduct(string $productId): array
    {
        return $this->select()
            ->where('product_id', '=', $productId)
            ->orderBy('created_at', 'DESC')
            ->fetchAll();
    }

    public function findByUser(string $userId): array
    {
        return $this->select()
            ->where('user_id', '=', $userId)
            ->orderBy('created_at', 'DESC')
            ->fetchAll();
    }

    public function findByRating(int $rating): array
    {
        return $this->select()
            ->where('rating', '=', $rating)
            ->fetchAll();
    }
}
