<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Orm\Repository\AbstractRepository;

#[AsService]
final class DemoProductRepository extends AbstractRepository
{
    private const ORDERABLE_COLUMNS = [
        'name' => 'name',
        'price' => 'price',
        'status' => 'status',
        'created_at' => 'created_at',
    ];

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

    public function countByTenant(string $tenantId): int
    {
        return $this->select()
            ->where('tenant_id', '=', $tenantId)
            ->count();
    }

    public function countAll(): int
    {
        return $this->select()->count();
    }

    public function findPage(int $limit, int $offset = 0): array
    {
        return $this->select()
            ->limit(max(1, $limit))
            ->offset(max(0, $offset))
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

    public function findFiltered(
        ?string $status = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?string $orderBy = null,
        int $limit = 10,
        int $offset = 0,
    ): array {
        $query = $this->select();

        if ($status !== null) {
            $query->where('status', '=', $status);
        }
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        $query->orderBy(self::ORDERABLE_COLUMNS[$orderBy ?? 'name'] ?? 'name', 'ASC');

        return $query
            ->limit(max(1, $limit))
            ->offset(max(0, $offset))
            ->fetchAll();
    }

    public function countFiltered(
        ?string $status = null,
        ?float $minPrice = null,
        ?float $maxPrice = null,
    ): int {
        $query = $this->select();

        if ($status !== null) {
            $query->where('status', '=', $status);
        }
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query->count();
    }
}
