<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoProductTableModel::class, domainModel: DemoProductResource::class)]
final class DemoProductMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoProductTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoProductResource();
        $resource->id = $tableModel->id;
        $resource->tenant_id = $tableModel->tenant_id;
        $resource->name = $tableModel->name;
        $resource->description = $tableModel->description;
        $resource->price = $tableModel->price;
        $resource->status = $tableModel->status;
        $resource->category_id = $tableModel->category_id;
        $resource->deleted_at = $tableModel->deleted_at;
        $resource->created_at = $tableModel->created_at;
        $resource->updated_at = $tableModel->updated_at;
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoProductResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoProductTableModel(
            id: $domainModel->id,
            tenant_id: $domainModel->tenant_id,
            name: $domainModel->name,
            description: $domainModel->description,
            price: $domainModel->price,
            status: $domainModel->status,
            category_id: $domainModel->category_id,
            deleted_at: $domainModel->deleted_at,
            created_at: $domainModel->created_at,
            updated_at: $domainModel->updated_at,
        );
    }
}
