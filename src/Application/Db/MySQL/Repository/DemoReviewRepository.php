<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\SatisfiesRepositoryContract;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Query\SystemScopeToken;
use Semitexa\Demo\Domain\Repository\DemoReviewRepositoryInterface;
use Semitexa\Orm\Repository\DomainRepository;
use Semitexa\Tenancy\Context\TenantContext;

#[AsRepository]
#[SatisfiesRepositoryContract(of: DemoReviewRepositoryInterface::class)]
final class DemoReviewRepository implements DemoReviewRepositoryInterface
{
    #[InjectAsReadonly]
    protected OrmManager $orm;

    private ?DomainRepository $repository = null;
    private ?SystemScopeToken $systemScopeToken = null;

    public function findById(string $id): ?DemoReview
    {
        /** @var DemoReview|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoReview $entity): DemoReview
    {
        /** @var DemoReview */
        return $entity->getId() === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    /** @return list<DemoReview> */
    public function findAll(int $limit = 100): array
    {
        /** @var list<DemoReview> */
        return $this->repository()->findAll(max(1, $limit));
    }

    /** @return list<DemoReview> */
    public function findByProduct(string $productId): array
    {
        /** @var list<DemoReview> */
        return $this->repository()->query()
            ->where(DemoReviewResource::column('productId'), Operator::Equals, $productId)
            ->orderBy(DemoReviewResource::column('createdAt'), Direction::Desc)
            ->fetchAllAs(DemoReview::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoReview> */
    public function findByUser(string $userId): array
    {
        /** @var list<DemoReview> */
        return $this->repository()->query()
            ->where(DemoReviewResource::column('userId'), Operator::Equals, $userId)
            ->orderBy(DemoReviewResource::column('createdAt'), Direction::Desc)
            ->fetchAllAs(DemoReview::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoReview> */
    public function findByRating(int $rating): array
    {
        /** @var list<DemoReview> */
        return $this->repository()->query()
            ->where(DemoReviewResource::column('rating'), Operator::Equals, $rating)
            ->fetchAllAs(DemoReview::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->orm()->repository(DemoReviewResource::class, DemoReview::class);
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
}
