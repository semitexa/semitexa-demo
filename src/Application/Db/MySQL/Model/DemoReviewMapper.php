<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Model;

use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(tableModel: DemoReviewTableModel::class, domainModel: DemoReviewResource::class)]
final class DemoReviewMapper implements TableModelMapper
{
    public function toDomain(object $tableModel): object
    {
        $tableModel instanceof DemoReviewTableModel || throw new \InvalidArgumentException('Unexpected table model.');
        $resource = new DemoReviewResource();
        $resource->id = $tableModel->id;
        $resource->tenant_id = $tableModel->tenant_id;
        $resource->product_id = $tableModel->product_id;
        $resource->user_id = $tableModel->user_id;
        $resource->rating = $tableModel->rating;
        $resource->body = $tableModel->body;
        $resource->created_at = $tableModel->created_at;
        $resource->updated_at = $tableModel->updated_at;
        return $resource;
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoReviewResource || throw new \InvalidArgumentException('Unexpected resource model.');
        return new DemoReviewTableModel(
            id: $domainModel->id,
            tenant_id: $domainModel->tenant_id,
            product_id: $domainModel->product_id,
            user_id: $domainModel->user_id,
            rating: $domainModel->rating,
            body: $domainModel->body,
            created_at: $domainModel->created_at,
            updated_at: $domainModel->updated_at,
        );
    }
}
