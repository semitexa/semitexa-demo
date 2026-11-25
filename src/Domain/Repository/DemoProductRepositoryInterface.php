<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\Repository\PaginatedResult;

interface DemoProductRepositoryInterface
{
    public function findById(string $id): ?DemoProduct;

    public function save(DemoProduct $entity): DemoProduct;

    public function delete(DemoProduct $entity): void;

    /** @return list<DemoProduct> */
    public function findByTenant(string $tenantId, int $limit = 100): array;

    public function countByTenant(string $tenantId): int;

    /**
     * Product counts for several tenants in one query; a tenant with no rows is 0.
     *
     * @param list<string> $tenantIds
     * @return array<string, int>
     */
    public function countByTenants(array $tenantIds): array;

    /**
     * One page of products and the counts a pager needs, read through the
     * tenant scope like every other read.
     *
     * @return PaginatedResult<DemoProduct>
     */
    public function paginate(int $page, int $perPage): PaginatedResult;

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
