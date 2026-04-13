<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Query\SystemScopeToken;
use Semitexa\Orm\Repository\DomainRepository;
use Semitexa\Tenancy\Context\TenantContext;

#[AsRepository]
final class DemoProductRepository
{
    private const ORDERABLE_COLUMNS = [
        'name' => 'name',
        'price' => 'price',
        'status' => 'status',
        'created_at' => 'createdAt',
    ];

    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;
    private ?SystemScopeToken $systemScopeToken = null;

    public function findById(string $id): ?DemoProduct
    {
        /** @var DemoProduct|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoProduct $entity): DemoProduct
    {
        /** @var DemoProduct */
        return $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    public function delete(DemoProduct $entity): void
    {
        $this->repository()->delete($entity);
    }

    /** @return list<DemoProduct> */
    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoProduct> */
        return $this->repository()->query()
            ->where(DemoProductResource::column('tenantId'), Operator::Equals, $tenantId)
            ->limit(max(1, $limit))
            ->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    public function countByTenant(string $tenantId): int
    {
        return (int) ($this->adapter()->execute(
            'SELECT COUNT(*) AS total FROM demo_products WHERE tenant_id = :tenant_id',
            ['tenant_id' => $tenantId],
        )->rows[0]['total'] ?? 0);
    }

    public function countAll(): int
    {
        return (int) ($this->adapter()->execute('SELECT COUNT(*) AS total FROM demo_products', [])->rows[0]['total'] ?? 0);
    }

    /** @return list<DemoProduct> */
    public function findPage(int $limit, int $offset = 0): array
    {
        /** @var list<DemoProduct> */
        return $this->repository()->query()
            ->limit(max(1, $limit))
            ->offset(max(0, $offset))
            ->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoProduct> */
    public function findByCategory(string $categoryId): array
    {
        /** @var list<DemoProduct> */
        return $this->repository()->query()
            ->where(DemoProductResource::column('categoryId'), Operator::Equals, $categoryId)
            ->orderBy(DemoProductResource::column('name'), Direction::Asc)
            ->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoProduct> */
    public function findByStatus(string $status): array
    {
        /** @var list<DemoProduct> */
        return $this->repository()->query()
            ->where(DemoProductResource::column('status'), Operator::Equals, $status)
            ->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoProduct> */
    public function search(string $term, int $limit = 50): array
    {
        /** @var list<DemoProduct> */
        return $this->repository()->query()
            ->where(DemoProductResource::column('name'), Operator::Like, "%{$term}%")
            ->limit($limit)
            ->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoProduct> */
    public function findFiltered(?string $status = null, ?float $minPrice = null, ?float $maxPrice = null, ?string $orderBy = null, int $limit = 10, int $offset = 0): array
    {
        $query = $this->repository()->query();
        if ($status !== null) {
            $query->where(DemoProductResource::column('status'), Operator::Equals, $status);
        }
        if ($minPrice !== null) {
            $query->where(DemoProductResource::column('price'), Operator::GreaterThanOrEquals, $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where(DemoProductResource::column('price'), Operator::LessThanOrEquals, $maxPrice);
        }
        $query->orderBy(
            DemoProductResource::column(self::ORDERABLE_COLUMNS[$orderBy ?? 'name'] ?? 'name'),
            Direction::Asc,
        );

        /** @var list<DemoProduct> */
        return $query
            ->limit(max(1, $limit))
            ->offset(max(0, $offset))
            ->fetchAllAs(DemoProduct::class, $this->orm()->getMapperRegistry());
    }

    public function countFiltered(?string $status = null, ?float $minPrice = null, ?float $maxPrice = null): int
    {
        $conditions = [];
        $params = [];

        if ($status !== null) {
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }
        if ($minPrice !== null) {
            $conditions[] = 'price >= :min_price';
            $params['min_price'] = $minPrice;
        }
        if ($maxPrice !== null) {
            $conditions[] = 'price <= :max_price';
            $params['max_price'] = $maxPrice;
        }

        $sql = 'SELECT COUNT(*) AS total FROM demo_products';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        return (int) ($this->adapter()->execute($sql, $params)->rows[0]['total'] ?? 0);
    }

    private function repository(): DomainRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->orm()->repository(DemoProductResource::class, DemoProduct::class);
        }

        $tenantId = TenantContext::get()?->getTenantId();
        if ($tenantId !== null && $tenantId !== '' && $tenantId !== 'default') {
            return $this->repository->forTenant($tenantId);
        }

        $systemScopeToken = $this->systemScopeToken ??= SystemScopeToken::issue();

        return $this->repository->withoutTenantScope($systemScopeToken);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function adapter(): \Semitexa\Orm\Adapter\DatabaseAdapterInterface
    {
        return $this->orm()->getAdapter();
    }
}
