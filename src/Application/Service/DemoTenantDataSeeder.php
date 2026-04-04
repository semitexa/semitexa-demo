<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;

/**
 * Reports per-tenant data counts for the tenancy isolation demo.
 * Does not seed additional data — relies on DemoDataSeeder having run.
 */
#[AsService]
final class DemoTenantDataSeeder
{
    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    /**
     * Get product count for a given tenant.
     */
    public function getProductCount(string $tenantId): int
    {
        return $this->productRepository->countByTenant($tenantId);
    }

    /**
     * Get a sample product listing for a given tenant (max $limit items).
     *
     * @return list<object>
     */
    public function getProducts(string $tenantId, int $limit = 5): array
    {
        return $this->productRepository->findByTenant($tenantId, $limit);
    }

    /**
     * Return the SQL that the ORM generates for this query (illustrative).
     */
    public function getIllustrationSql(string $tenantId): string
    {
        return "SELECT * FROM demo_products\nWHERE tenant_id = ?\nLIMIT 5";
    }
}
