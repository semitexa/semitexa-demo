<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoOrderResource;
use Semitexa\Demo\Domain\Model\DemoOrder;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoOrderResource::class,
    domainModel: DemoOrder::class
)]
final class DemoOrderMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoOrderResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoOrder(
            id: $resource->id,
            tenantId: $resource->tenantId,
            userId: $resource->userId,
            status: $resource->status,
            totalAmount: $resource->totalAmount,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoOrder || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoOrderResource(
            id: $domainModel->id,
            tenantId: $domainModel->tenantId,
            userId: $domainModel->userId,
            status: $domainModel->status,
            totalAmount: $domainModel->totalAmount,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
