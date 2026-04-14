<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoOrderResource;
use Semitexa\Demo\Domain\Model\DemoOrder;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoOrderResource::class,
    domainModel: DemoOrder::class
)]
final class DemoOrderMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoOrderResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoOrder();
        $domain->setId($resource->id);
        $domain->setTenantId($resource->tenantId);
        $domain->setUserId($resource->userId);
        $domain->setStatus($resource->status);
        $domain->setTotalAmount($resource->totalAmount);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoOrder || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoOrderResource(
            id: $domainModel->getId(),
            tenantId: $domainModel->getTenantId(),
            userId: $domainModel->getUserId(),
            status: $domainModel->getStatus(),
            totalAmount: $domainModel->getTotalAmount(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
