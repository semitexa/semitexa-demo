<?php

declare(strict_types=1);

namespace Semitexa\Demo\Domain\Repository;

use Semitexa\Demo\Domain\Model\DemoAiTask;

interface DemoAiTaskRepositoryInterface
{
    public function findById(string $id): ?DemoAiTask;

    public function save(DemoAiTask $entity): DemoAiTask;

    /** @return list<DemoAiTask> */
    public function findByTenant(string $tenantId, int $limit = 100): array;

    /** @return list<DemoAiTask> */
    public function findPending(int $limit = 10): array;

    /** @return list<DemoAiTask> */
    public function findByStatus(string $status): array;

    /**
     * Tasks in any of the given statuses, newest first; at most $limit when given.
     *
     * @param list<string> $statuses
     * @return list<DemoAiTask>
     */
    public function findByStatuses(array $statuses, ?int $limit = null): array;

    /**
     * How many tasks are in each of the given statuses, in one grouped query;
     * a status with none is absent.
     *
     * @param list<string> $statuses
     * @return array<string, int>
     */
    public function countByStatuses(array $statuses): array;

    public function updateStatus(string $id, string $status): bool;

    public function updateStageResults(string $id, string $stageResultsJson): bool;
}
