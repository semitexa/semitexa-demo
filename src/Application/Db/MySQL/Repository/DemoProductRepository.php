<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Orm\Repository\AbstractRepository;

final class DemoProductRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoProductResource::class;
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        return $this->select()
            ->where('tenant_id', '=', $tenantId)
            ->limit(max(1, $limit))
            ->fetchAll();
    }

    public function findByCategory(string $categoryId): array
    {
        return $this->select()
            ->where('category_id', '=', $categoryId)
            ->orderBy('name', 'ASC')
            ->fetchAll();
    }

    public function findByStatus(string $status): array
    {
        return $this->select()
            ->where('status', '=', $status)
            ->fetchAll();
    }

    public function search(string $term, int $limit = 50): array
    {
        return $this->select()
            ->where('name', 'LIKE', "%{$term}%")
            ->limit($limit)
            ->fetchAll();
    }
}
