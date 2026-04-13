<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoCategoryResource::class,
    domainModel: DemoCategory::class
)]
final class DemoCategoryMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoCategoryResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoCategory(
            id: $resource->id,
            name: $resource->name,
            slug: $resource->slug,
            description: $resource->description,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoCategory || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoCategoryResource(
            id: $domainModel->id,
            name: $domainModel->name,
            slug: $domainModel->slug,
            description: $domainModel->description,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
