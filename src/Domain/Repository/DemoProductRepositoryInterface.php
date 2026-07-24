<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoProduct;

interface DemoProductRepositoryInterface
{
    public function findById(string $id): ?DemoProduct;

    public function save(DemoProduct $entity): DemoProduct;

    public function delete(DemoProduct $entity): void;

    /** @return list<DemoProduct> */
    public function findByTenant(string $tenantId, int $limit = 100): array;

    public function countByTenant(string $tenantId): int;

    public function countAll(): int;

    /** @return list<DemoProduct> */
    public function findPage(int $limit, int $offset = 0): array;

    /** @return list<DemoProduct> */
    public function findByCategory(string $categoryId): array;

    /** @return list<DemoProduct> */
    public function findByStatus(string $status): array;

    /** @return list<DemoProduct> */
    public function search(string $term, int $limit = 50): array;

    /** @return list<DemoProduct> */
    public function findFiltered(?string $status = null, ?float $minPrice = null, ?float $maxPrice = null, ?string $orderBy = null, int $limit = 10, int $offset = 0): array;

    public function countFiltered(?string $status = null, ?float $minPrice = null, ?float $maxPrice = null): int;
}
