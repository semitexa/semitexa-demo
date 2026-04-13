<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotResource;
use Semitexa\Demo\Domain\Model\DemoAnalyticsSnapshot;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoAnalyticsSnapshotResource::class,
    domainModel: DemoAnalyticsSnapshot::class
)]
final class DemoAnalyticsSnapshotMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoAnalyticsSnapshotResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoAnalyticsSnapshot(
            id: $resource->id,
            tenantId: $resource->tenantId,
            metricType: $resource->metricType,
            value: (float) $resource->value,
            periodStart: $resource->periodStart,
            periodEnd: $resource->periodEnd,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoAnalyticsSnapshot || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoAnalyticsSnapshotResource(
            id: $domainModel->id,
            tenantId: $domainModel->tenantId,
            metricType: $domainModel->metricType,
            value: number_format($domainModel->value, 4, '.', ''),
            periodStart: $domainModel->periodStart,
            periodEnd: $domainModel->periodEnd,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
