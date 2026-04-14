<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoCategoryResource::class,
    domainModel: DemoCategory::class
)]
final class DemoCategoryMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoCategoryResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoCategory();
        $domain->setId($resource->id);
        $domain->setName($resource->name);
        $domain->setSlug($resource->slug);
        $domain->setDescription($resource->description);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoCategory || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoCategoryResource(
            id: $domainModel->getId(),
            name: $domainModel->getName(),
            slug: $domainModel->getSlug(),
            description: $domainModel->getDescription(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
