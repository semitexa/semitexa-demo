<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskResource;
use Semitexa\Demo\Domain\Model\DemoAiTask;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoAiTaskResource::class,
    domainModel: DemoAiTask::class
)]
final class DemoAiTaskMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoAiTaskResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoAiTask();
        $domain->setId($resource->id);
        $domain->setTenantId($resource->tenantId);
        $domain->setInputText($resource->inputText);
        $domain->setStatus($resource->status);
        $domain->setStages($resource->stages);
        $domain->setStageResults($resource->stageResults);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoAiTask || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoAiTaskResource(
            id: $domainModel->getId(),
            tenantId: $domainModel->getTenantId(),
            inputText: $domainModel->getInputText(),
            status: $domainModel->getStatus(),
            stages: $domainModel->getStages(),
            stageResults: $domainModel->getStageResults(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
