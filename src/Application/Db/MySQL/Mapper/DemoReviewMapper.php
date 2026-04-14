<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoReviewResource::class,
    domainModel: DemoReview::class
)]
final class DemoReviewMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoReviewResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoReview();
        $domain->setId($resource->id);
        $domain->setTenantId($resource->tenantId);
        $domain->setProductId($resource->productId);
        $domain->setUserId($resource->userId);
        $domain->setRating($resource->rating);
        $domain->setBody($resource->body);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoReview || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoReviewResource(
            id: $domainModel->getId(),
            tenantId: $domainModel->getTenantId(),
            productId: $domainModel->getProductId(),
            userId: $domainModel->getUserId(),
            rating: $domainModel->getRating(),
            body: $domainModel->getBody(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
