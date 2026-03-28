<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoOrderResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoOrderTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoOrderRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoOrderResource
    {
        /** @var DemoOrderResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoOrderResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findAll(int $limit = 100): array
    {
        /** @var list<DemoOrderResource> */
        return $this->repository()->findAll(max(1, $limit));
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoOrderResource> */
        return $this->repository()->query()
            ->where(DemoOrderTableModel::column('tenant_id'), Operator::Equals, $tenantId)
            ->limit($limit)
            ->fetchAllAs(DemoOrderResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByUser(string $userId): array
    {
        /** @var list<DemoOrderResource> */
        return $this->repository()->query()
            ->where(DemoOrderTableModel::column('user_id'), Operator::Equals, $userId)
            ->orderBy(DemoOrderTableModel::column('created_at'), Direction::Desc)
            ->fetchAllAs(DemoOrderResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByStatus(string $status): array
    {
        /** @var list<DemoOrderResource> */
        return $this->repository()->query()
            ->where(DemoOrderTableModel::column('status'), Operator::Equals, $status)
            ->fetchAllAs(DemoOrderResource::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoOrderTableModel::class, DemoOrderResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function copyInto(object $source, DemoOrderResource $target): void
    {
        $source instanceof DemoOrderResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
