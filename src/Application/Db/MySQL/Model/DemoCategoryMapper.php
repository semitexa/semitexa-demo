<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoCategoryTableModel::class, domainModel: DemoCategoryResource::class)]
final class DemoCategoryMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoCategoryTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoCategoryResource();
        $resource->id = $tableModel->id;
        $resource->name = $tableModel->name;
        $resource->slug = $tableModel->slug;
        $resource->description = $tableModel->description;
        $resource->created_at = $tableModel->created_at;
        $resource->updated_at = $tableModel->updated_at;
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoCategoryResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoCategoryTableModel(
            id: $domainModel->id,
            name: $domainModel->name,
            slug: $domainModel->slug,
            description: $domainModel->description,
            created_at: $domainModel->created_at,
            updated_at: $domainModel->updated_at,
        );
    }
}
