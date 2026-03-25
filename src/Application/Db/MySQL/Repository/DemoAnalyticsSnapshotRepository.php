<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotResource;
use Semitexa\Orm\Repository\AbstractRepository;

final class DemoAnalyticsSnapshotRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoAnalyticsSnapshotResource::class;
    }

    public function findByMetricAndTenant(string $metricType, string $tenantId, int $limit = 10): array
    {
        return $this->select()
            ->where('metric_type', '=', $metricType)
            ->where('tenant_id', '=', $tenantId)
            ->orderBy('recorded_at', 'DESC')
            ->limit($limit)
            ->fetchAll();
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        return $this->select()
            ->where('tenant_id', '=', $tenantId)
            ->orderBy('recorded_at', 'DESC')
            ->limit($limit)
            ->fetchAll();
    }
}
