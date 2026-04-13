<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoReviewResource::class,
    domainModel: DemoReview::class
)]
final class DemoReviewMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoReviewResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoReview(
            id: $resource->id,
            tenantId: $resource->tenantId,
            productId: $resource->productId,
            userId: $resource->userId,
            rating: $resource->rating,
            body: $resource->body,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoReview || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoReviewResource(
            id: $domainModel->id,
            tenantId: $domainModel->tenantId,
            productId: $domainModel->productId,
            userId: $domainModel->userId,
            rating: $domainModel->rating,
            body: $domainModel->body,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
