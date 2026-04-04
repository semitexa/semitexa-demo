<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoAnalyticsSnapshotRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoAnalyticsSnapshotResource
    {
        /** @var DemoAnalyticsSnapshotResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoAnalyticsSnapshotResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findByMetricAndTenant(string $metricType, string $tenantId, int $limit = 10): array
    {
        /** @var list<DemoAnalyticsSnapshotResource> */
        return $this->repository()->query()
            ->where(DemoAnalyticsSnapshotTableModel::column('metric_type'), Operator::Equals, $metricType)
            ->where(DemoAnalyticsSnapshotTableModel::column('tenant_id'), Operator::Equals, $tenantId)
            ->orderBy(DemoAnalyticsSnapshotTableModel::column('period_end'), Direction::Desc)
            ->limit($limit)
            ->fetchAllAs(DemoAnalyticsSnapshotResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoAnalyticsSnapshotResource> */
        return $this->repository()->query()
            ->where(DemoAnalyticsSnapshotTableModel::column('tenant_id'), Operator::Equals, $tenantId)
            ->orderBy(DemoAnalyticsSnapshotTableModel::column('period_end'), Direction::Desc)
            ->limit($limit)
            ->fetchAllAs(DemoAnalyticsSnapshotResource::class, $this->orm()->getMapperRegistry());
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoAnalyticsSnapshotTableModel::class, DemoAnalyticsSnapshotResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function copyInto(object $source, DemoAnalyticsSnapshotResource $target): void
    {
        $source instanceof DemoAnalyticsSnapshotResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
