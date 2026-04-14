<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\SatisfiesRepositoryContract;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoOrderResource;
use Semitexa\Demo\Domain\Model\DemoOrder;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Query\SystemScopeToken;
use Semitexa\Demo\Domain\Repository\DemoOrderRepositoryInterface;
use Semitexa\Orm\Repository\DomainRepository;
use Semitexa\Tenancy\Context\TenantContext;

#[AsRepository]
#[SatisfiesRepositoryContract(of: DemoOrderRepositoryInterface::class)]
final class DemoOrderRepository implements DemoOrderRepositoryInterface
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;
    private ?SystemScopeToken $systemScopeToken = null;

    public function findById(string $id): ?DemoOrder
    {
        /** @var DemoOrder|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoOrder $entity): DemoOrder
    {
        /** @var DemoOrder */
        return $entity->getId() === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    /** @return list<DemoOrder> */
    public function findAll(int $limit = 100): array
    {
        /** @var list<DemoOrder> */
        return $this->repository()->findAll(max(1, $limit));
    }

    /** @return list<DemoOrder> */
    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoOrder> */
        return $this->repository()->query()
            ->where(DemoOrderResource::column('tenantId'), Operator::Equals, $tenantId)
            ->limit($limit)
            ->fetchAllAs(DemoOrder::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoOrder> */
    public function findByUser(string $userId): array
    {
        /** @var list<DemoOrder> */
        return $this->repository()->query()
            ->where(DemoOrderResource::column('userId'), Operator::Equals, $userId)
            ->orderBy(DemoOrderResource::column('createdAt'), Direction::Desc)
            ->fetchAllAs(DemoOrder::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoOrder> */
    public function findByStatus(string $status): array
    {
        /** @var list<DemoOrder> */
        return $this->repository()->query()
            ->where(DemoOrderResource::column('status'), Operator::Equals, $status)
            ->fetchAllAs(DemoOrder::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->orm()->repository(DemoOrderResource::class, DemoOrder::class);
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
