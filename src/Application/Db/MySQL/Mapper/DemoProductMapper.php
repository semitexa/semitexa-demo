<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoProductResource::class,
    domainModel: DemoProduct::class
)]
final class DemoProductMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoProductResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoProduct(
            id: $resource->id,
            tenantId: $resource->tenantId,
            name: $resource->name,
            description: $resource->description,
            price: $resource->price,
            status: $resource->status,
            categoryId: $resource->categoryId,
            deletedAt: $resource->deletedAt,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoProduct || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoProductResource(
            id: $domainModel->id,
            tenantId: $domainModel->tenantId,
            name: $domainModel->name,
            description: $domainModel->description,
            price: $domainModel->price,
            status: $domainModel->status,
            categoryId: $domainModel->categoryId,
            deletedAt: $domainModel->deletedAt,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
