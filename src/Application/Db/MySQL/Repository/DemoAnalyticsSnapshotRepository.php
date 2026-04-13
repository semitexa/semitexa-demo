<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotResource;
use Semitexa\Demo\Domain\Model\DemoAnalyticsSnapshot;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Query\SystemScopeToken;
use Semitexa\Orm\Repository\DomainRepository;
use Semitexa\Tenancy\Context\TenantContext;

#[AsRepository]
final class DemoAnalyticsSnapshotRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;
    private ?SystemScopeToken $systemScopeToken = null;

    public function findById(string $id): ?DemoAnalyticsSnapshot
    {
        /** @var DemoAnalyticsSnapshot|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoAnalyticsSnapshot $entity): DemoAnalyticsSnapshot
    {
        /** @var DemoAnalyticsSnapshot */
        return $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    /** @return list<DemoAnalyticsSnapshot> */
    public function findByMetricAndTenant(string $metricType, string $tenantId, int $limit = 10): array
    {
        /** @var list<DemoAnalyticsSnapshot> */
        return $this->repository()->query()
            ->where(DemoAnalyticsSnapshotResource::column('metricType'), Operator::Equals, $metricType)
            ->where(DemoAnalyticsSnapshotResource::column('tenantId'), Operator::Equals, $tenantId)
            ->orderBy(DemoAnalyticsSnapshotResource::column('periodEnd'), Direction::Desc)
            ->limit($limit)
            ->fetchAllAs(DemoAnalyticsSnapshot::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoAnalyticsSnapshot> */
    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoAnalyticsSnapshot> */
        return $this->repository()->query()
            ->where(DemoAnalyticsSnapshotResource::column('tenantId'), Operator::Equals, $tenantId)
            ->orderBy(DemoAnalyticsSnapshotResource::column('periodEnd'), Direction::Desc)
            ->limit($limit)
            ->fetchAllAs(DemoAnalyticsSnapshot::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->orm()->repository(DemoAnalyticsSnapshotResource::class, DemoAnalyticsSnapshot::class);
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
