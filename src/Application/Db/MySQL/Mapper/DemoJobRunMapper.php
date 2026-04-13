<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoJobRunResource;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\TableModelMapper;

#[AsMapper(
    resourceModel: DemoJobRunResource::class,
    domainModel: DemoJobRun::class
)]
final class DemoJobRunMapper implements TableModelMapper
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoJobRunResource || throw new \InvalidArgumentException('Unexpected resource model.');

        return new DemoJobRun(
            id: $resource->id,
            jobType: $resource->jobType,
            schedulerRunId: $resource->schedulerRunId,
            status: $resource->status,
            progressPercent: $resource->progressPercent,
            progressMessage: $resource->progressMessage,
            resultPayload: $resource->resultPayload,
            attemptNumber: $resource->attemptNumber,
            createdAt: $resource->createdAt,
            updatedAt: $resource->updatedAt,
        );
    }

    public function toTableModel(object $domainModel): object
    {
        $domainModel instanceof DemoJobRun || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoJobRunResource(
            id: $domainModel->id,
            jobType: $domainModel->jobType,
            schedulerRunId: $domainModel->schedulerRunId,
            status: $domainModel->status,
            progressPercent: $domainModel->progressPercent,
            progressMessage: $domainModel->progressMessage,
            resultPayload: $domainModel->resultPayload,
            attemptNumber: $domainModel->attemptNumber,
            createdAt: $domainModel->createdAt,
            updatedAt: $domainModel->updatedAt,
        );
    }
}
