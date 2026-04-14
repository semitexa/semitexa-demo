<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoProductResource::class,
    domainModel: DemoProduct::class
)]
final class DemoProductMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoProductResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoProduct();
        $domain->setId($resource->id);
        $domain->setTenantId($resource->tenantId);
        $domain->setName($resource->name);
        $domain->setDescription($resource->description);
        $domain->setPrice($resource->price);
        $domain->setStatus($resource->status);
        $domain->setCategoryId($resource->categoryId);
        $domain->setDeletedAt($resource->deletedAt);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoProduct || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoProductResource(
            id: $domainModel->getId(),
            tenantId: $domainModel->getTenantId(),
            name: $domainModel->getName(),
            description: $domainModel->getDescription(),
            price: $domainModel->getPrice(),
            status: $domainModel->getStatus(),
            categoryId: $domainModel->getCategoryId(),
            deletedAt: $domainModel->getDeletedAt(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
