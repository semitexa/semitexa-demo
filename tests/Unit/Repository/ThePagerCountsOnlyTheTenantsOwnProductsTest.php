<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Repository;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\Domain\Model\ConnectionConfig;
use Semitexa\Orm\OrmManager;
use Semitexa\Tenancy\Context\TenantContext;

/**
 * The pagination page took its total from a raw COUNT(*) over demo_products,
 * past the tenant scope, while its rows came through the scope. On a tenant's
 * site the pager therefore counted every tenant's products: one product of
 * its own, "Page 1 of 3 with 15 total products". The page and its counts
 * now come from one paginate() through the scope
 * (tk-demo-product-count-ignores-the-tenant-scope).
 */
final class ThePagerCountsOnlyTheTenantsOwnProductsTest extends TestCase
{
    private DemoProductRepository $products;

    private ?TenantContext $tenantBefore = null;

    protected function setUp(): void
    {
        $this->tenantBefore = TenantContext::get();

        $orm = new OrmManager(config: new ConnectionConfig(driver: 'sqlite', sqliteMemory: true));
        $orm->getQueryAdapter()->execute('CREATE TABLE demo_products (
            id BLOB PRIMARY KEY,
            tenant_id TEXT NULL,
            name TEXT NOT NULL,
            description TEXT NULL,
            price TEXT NOT NULL,
            status TEXT NOT NULL,
            category_id BLOB NULL,
            deleted_at TEXT NULL,
            created_at TEXT NULL,
            updated_at TEXT NULL
        )', []);
        // Inserted out of name order, so the page order comes from the query.
        $rows = [
            ['globex', 'Hammer'], ['acme', 'Crate'], ['globex', 'Engine'], ['acme', 'Anvil'],
            ['globex', 'Gear'], ['globex', 'Dynamo'], ['acme', 'Bucket'], ['globex', 'Flywheel'],
        ];
        foreach ($rows as [$tenant, $name]) {
            $orm->getQueryAdapter()->execute(
                'INSERT INTO demo_products (id, tenant_id, name, price, status) VALUES (:id, :tenant, :name, :price, :status)',
                ['id' => random_bytes(16), 'tenant' => $tenant, 'name' => $name, 'price' => '1.00', 'status' => 'active'],
            );
        }

        $this->products = new DemoProductRepository();
        (new \ReflectionProperty($this->products, 'orm'))->setValue($this->products, $orm);
    }

    protected function tearDown(): void
    {
        if ($this->tenantBefore === null) {
            TenantContext::clear();
        } else {
            TenantContext::set($this->tenantBefore);
        }
    }

    #[Test]
    public function a_tenants_pager_counts_and_pages_only_that_tenants_products(): void
    {
        TenantContext::set(TenantContext::fromResolution('acme', 'test'));

        $first = $this->products->paginate(1, 2);
        $second = $this->products->paginate(2, 2);

        self::assertSame(3, $first->total, 'acme has three of the eight products');
        self::assertSame(2, $first->lastPage);
        self::assertSame(['Anvil', 'Bucket'], $this->names($first->items));
        self::assertSame(['Crate'], $this->names($second->items));
    }

    #[Test]
    public function the_default_site_pages_through_every_tenants_products(): void
    {
        TenantContext::clear();

        $result = $this->products->paginate(1, 5);

        self::assertSame(8, $result->total);
        self::assertSame(2, $result->lastPage);
        self::assertSame(['Anvil', 'Bucket', 'Crate', 'Dynamo', 'Engine'], $this->names($result->items));
    }

    /**
     * @param list<DemoProduct> $items
     *
     * @return list<string>
     */
    private function names(array $items): array
    {
        return array_map(static fn (DemoProduct $product): string => $product->getName(), $items);
    }
}
