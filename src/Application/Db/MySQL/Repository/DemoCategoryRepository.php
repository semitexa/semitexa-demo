<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Application\Db\MySQL\Table\DemoCategoryTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoCategoryRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoCategoryResource
    {
        /** @var DemoCategoryResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoCategoryResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findBySlug(string $slug): ?DemoCategoryResource
    {
        /** @var DemoCategoryResource|null */
        return $this->repository()->query()
            ->where(DemoCategoryTableModel::column('slug'), Operator::Equals, $slug)
            ->fetchOneAs(DemoCategoryResource::class, $this->orm()->getMapperRegistry());
    }

    public function findAllOrdered(): array
    {
        /** @var list<DemoCategoryResource> */
        return $this->repository()->query()
            ->orderBy(DemoCategoryTableModel::column('name'), Direction::Asc)
            ->fetchAllAs(DemoCategoryResource::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoCategoryTableModel::class, DemoCategoryResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function copyInto(object $source, DemoCategoryResource $target): void
    {
        $source instanceof DemoCategoryResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
