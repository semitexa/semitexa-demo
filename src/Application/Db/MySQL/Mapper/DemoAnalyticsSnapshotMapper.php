<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoAnalyticsSnapshotResource;
use Semitexa\Demo\Domain\Model\DemoAnalyticsSnapshot;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoAnalyticsSnapshotResource::class,
    domainModel: DemoAnalyticsSnapshot::class
)]
final class DemoAnalyticsSnapshotMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoAnalyticsSnapshotResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoAnalyticsSnapshot();
        $domain->setId($resource->id);
        $domain->setTenantId($resource->tenantId);
        $domain->setMetricType($resource->metricType);
        $domain->setValue((float) $resource->value);
        $domain->setPeriodStart($resource->periodStart);
        $domain->setPeriodEnd($resource->periodEnd);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoAnalyticsSnapshot || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoAnalyticsSnapshotResource(
            id: $domainModel->getId(),
            tenantId: $domainModel->getTenantId(),
            metricType: $domainModel->getMetricType(),
            value: number_format($domainModel->getValue(), 4, '.', ''),
            periodStart: $domainModel->getPeriodStart(),
            periodEnd: $domainModel->getPeriodEnd(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
