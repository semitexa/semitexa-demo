<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoJobRun;

interface DemoJobRunRepositoryInterface
{
    public function findById(string $id): ?DemoJobRun;

    public function save(DemoJobRun $entity): DemoJobRun;

    /** @return list<DemoJobRun> */
    public function findByJobType(string $jobType): array;

    /**
     * Runs of any of the given job types, newest first.
     *
     * @param list<string> $jobTypes
     * @return list<DemoJobRun>
     */
    public function findByJobTypes(array $jobTypes): array;

    /** @return list<DemoJobRun> */
    public function findActiveRuns(): array;

    /** @return list<DemoJobRun> */
    public function findBySchedulerRun(string $schedulerRunId): array;

    public function updateProgress(string $id, int $percent, ?string $message = null): void;

    public function markCompleted(string $id, ?string $resultPayload = null): void;

    public function markFailed(string $id, string $errorMessage): void;
}
