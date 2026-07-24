<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoAnalyticsSnapshot;

interface DemoAnalyticsSnapshotRepositoryInterface
{
    public function findById(string $id): ?DemoAnalyticsSnapshot;

    public function save(DemoAnalyticsSnapshot $entity): DemoAnalyticsSnapshot;

    /** @return list<DemoAnalyticsSnapshot> */
    public function findByMetricAndTenant(string $metricType, string $tenantId, int $limit = 10): array;

    /** @return list<DemoAnalyticsSnapshot> */
    public function findByTenant(string $tenantId, int $limit = 100): array;
}
