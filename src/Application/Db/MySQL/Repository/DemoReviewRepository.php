<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoReviewRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoReviewResource
    {
        /** @var DemoReviewResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoReviewResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findAll(int $limit = 100): array
    {
        /** @var list<DemoReviewResource> */
        return $this->repository()->findAll(max(1, $limit));
    }

    public function findByProduct(string $productId): array
    {
        /** @var list<DemoReviewResource> */
        return $this->repository()->query()
            ->where(DemoReviewTableModel::column('product_id'), Operator::Equals, $productId)
            ->orderBy(DemoReviewTableModel::column('created_at'), Direction::Desc)
            ->fetchAllAs(DemoReviewResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByUser(string $userId): array
    {
        /** @var list<DemoReviewResource> */
        return $this->repository()->query()
            ->where(DemoReviewTableModel::column('user_id'), Operator::Equals, $userId)
            ->orderBy(DemoReviewTableModel::column('created_at'), Direction::Desc)
            ->fetchAllAs(DemoReviewResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByRating(int $rating): array
    {
        /** @var list<DemoReviewResource> */
        return $this->repository()->query()
            ->where(DemoReviewTableModel::column('rating'), Operator::Equals, $rating)
            ->fetchAllAs(DemoReviewResource::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoReviewTableModel::class, DemoReviewResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function copyInto(object $source, DemoReviewResource $target): void
    {
        $source instanceof DemoReviewResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
