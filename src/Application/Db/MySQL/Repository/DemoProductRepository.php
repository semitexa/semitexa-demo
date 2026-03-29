<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoProductRepository
{
    private const ORDERABLE_COLUMNS = [
        'name' => 'name',
        'price' => 'price',
        'status' => 'status',
        'created_at' => 'created_at',
    ];

    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoProductResource
    {
        /** @var DemoProductResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoProductResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoProductResource> */
        return $this->repository()->query()
            ->where(DemoProductTableModel::column('tenant_id'), Operator::Equals, $tenantId)
            ->limit(max(1, $limit))
            ->fetchAllAs(DemoProductResource::class, $this->orm()->getMapperRegistry());
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

    public function findPage(int $limit, int $offset = 0): array
    {
        /** @var list<DemoProductResource> */
        return $this->repository()->query()
            ->limit(max(1, $limit))
            ->offset(max(0, $offset))
            ->fetchAllAs(DemoProductResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByCategory(string $categoryId): array
    {
        /** @var list<DemoProductResource> */
        return $this->repository()->query()
            ->where(DemoProductTableModel::column('category_id'), Operator::Equals, $categoryId)
            ->orderBy(DemoProductTableModel::column('name'), Direction::Asc)
            ->fetchAllAs(DemoProductResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByStatus(string $status): array
    {
        /** @var list<DemoProductResource> */
        return $this->repository()->query()
            ->where(DemoProductTableModel::column('status'), Operator::Equals, $status)
            ->fetchAllAs(DemoProductResource::class, $this->orm()->getMapperRegistry());
    }

    public function search(string $term, int $limit = 50): array
    {
        /** @var list<DemoProductResource> */
        return $this->repository()->query()
            ->where(DemoProductTableModel::column('name'), Operator::Like, "%{$term}%")
            ->limit($limit)
            ->fetchAllAs(DemoProductResource::class, $this->orm()->getMapperRegistry());
    }

    public function findFiltered(?string $status = null, ?float $minPrice = null, ?float $maxPrice = null, ?string $orderBy = null, int $limit = 10, int $offset = 0): array
    {
        $query = $this->repository()->query();
        if ($status !== null) {
            $query->where(DemoProductTableModel::column('status'), Operator::Equals, $status);
        }
        if ($minPrice !== null) {
            $query->where(DemoProductTableModel::column('price'), Operator::GreaterThanOrEquals, $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where(DemoProductTableModel::column('price'), Operator::LessThanOrEquals, $maxPrice);
        }
        $query->orderBy(
            DemoProductTableModel::column(self::ORDERABLE_COLUMNS[$orderBy ?? 'name'] ?? 'name'),
            Direction::Asc,
        );

        /** @var list<DemoProductResource> */
        return $query
            ->limit(max(1, $limit))
            ->offset(max(0, $offset))
            ->fetchAllAs(DemoProductResource::class, $this->orm()->getMapperRegistry());
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
        return $this->repository ??= $this->orm()->repository(DemoProductTableModel::class, DemoProductResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function adapter(): \Semitexa\Orm\Adapter\DatabaseAdapterInterface
    {
        return $this->orm()->getAdapter();
    }

    private function copyInto(object $source, DemoProductResource $target): void
    {
        $source instanceof DemoProductResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
