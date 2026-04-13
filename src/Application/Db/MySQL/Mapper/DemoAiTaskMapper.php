<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskResource;
use Semitexa\Demo\Domain\Model\DemoAiTask;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoAiTaskResource::class,
    domainModel: DemoAiTask::class
)]
final class DemoAiTaskMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoAiTaskResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoAiTask(
            id: $resource->id,
            tenantId: $resource->tenantId,
            inputText: $resource->inputText,
            status: $resource->status,
            stages: $resource->stages,
            stageResults: $resource->stageResults,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoAiTask || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoAiTaskResource(
            id: $domainModel->id,
            tenantId: $domainModel->tenantId,
            inputText: $domainModel->inputText,
            status: $domainModel->status,
            stages: $domainModel->stages,
            stageResults: $domainModel->stageResults,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
