<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\SatisfiesRepositoryContract;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Demo\Domain\Repository\DemoCategoryRepositoryInterface;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
#[SatisfiesRepositoryContract(of: DemoCategoryRepositoryInterface::class)]
final class DemoCategoryRepository implements DemoCategoryRepositoryInterface
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoCategory
    {
        /** @var DemoCategory|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoCategory $entity): DemoCategory
    {
        /** @var DemoCategory */
        return $entity->getId() === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    public function findBySlug(string $slug): ?DemoCategory
    {
        /** @var DemoCategory|null */
        return $this->repository()->query()
            ->where(DemoCategoryResource::column('slug'), Operator::Equals, $slug)
            ->fetchOneAs(DemoCategory::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoCategory> */
    public function findAllOrdered(): array
    {
        /** @var list<DemoCategory> */
        return $this->repository()->query()
            ->orderBy(DemoCategoryResource::column('name'), Direction::Asc)
            ->fetchAllAs(DemoCategory::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoCategoryResource::class, DemoCategory::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }
}
