<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Mapper;

use Semitexa\Demo\Application\Db\MySQL\Model\DemoJobRunResource;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Orm\Attribute\AsMapper;
use Semitexa\Orm\Contract\ResourceModelMapperInterface;

#[AsMapper(
    resourceModel: DemoJobRunResource::class,
    domainModel: DemoJobRun::class
)]
final class DemoJobRunMapper implements ResourceModelMapperInterface
{
    public function toDomain(object $resource): object
    {
        $resource instanceof DemoJobRunResource || throw new \InvalidArgumentException('Unexpected resource model.');

        $domain = new DemoJobRun();
        $domain->setId($resource->id);
        $domain->setJobType($resource->jobType);
        $domain->setSchedulerRunId($resource->schedulerRunId);
        $domain->setStatus($resource->status);
        $domain->setProgressPercent($resource->progressPercent);
        $domain->setProgressMessage($resource->progressMessage);
        $domain->setResultPayload($resource->resultPayload);
        $domain->setAttemptNumber($resource->attemptNumber);
        $domain->setCreatedAt($resource->createdAt);
        $domain->setUpdatedAt($resource->updatedAt);

        return $domain;
    }

    public function toSourceModel(object $domainModel): object
    {
        $domainModel instanceof DemoJobRun || throw new \InvalidArgumentException('Unexpected domain model.');

        return new DemoJobRunResource(
            id: $domainModel->getId(),
            jobType: $domainModel->getJobType(),
            schedulerRunId: $domainModel->getSchedulerRunId(),
            status: $domainModel->getStatus(),
            progressPercent: $domainModel->getProgressPercent(),
            progressMessage: $domainModel->getProgressMessage(),
            resultPayload: $domainModel->getResultPayload(),
            attemptNumber: $domainModel->getAttemptNumber(),
            createdAt: $domainModel->getCreatedAt(),
            updatedAt: $domainModel->getUpdatedAt(),
        );
    }
}
