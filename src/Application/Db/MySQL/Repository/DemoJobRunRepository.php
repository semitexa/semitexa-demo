<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoJobRunResource;
use Semitexa\Orm\Repository\AbstractRepository;

#[AsService]
final class DemoJobRunRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoJobRunResource::class;
    }

    public function findByJobType(string $jobType): array
    {
        return $this->select()
            ->where('job_type', '=', $jobType)
            ->orderBy('created_at', 'DESC')
            ->fetchAll();
    }

    public function findActiveRuns(): array
    {
        return $this->select()
            ->whereIn('status', ['pending', 'running'])
            ->orderBy('created_at', 'ASC')
            ->fetchAll();
    }

    public function findBySchedulerRun(string $schedulerRunId): array
    {
        return $this->select()
            ->where('scheduler_run_id', '=', $schedulerRunId)
            ->fetchAll();
    }

    public function updateProgress(string $id, int $percent, ?string $message = null): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }

        /** @var DemoJobRunResource $run */
        $run->progress_percent = max(0, min(100, $percent));
        if ($message !== null) {
            $run->progress_message = $message;
        }

        $this->save($run);
    }

    public function markCompleted(string $id, ?string $resultPayload = null): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }

        /** @var DemoJobRunResource $run */
        $run->status = 'completed';
        $run->progress_percent = 100;
        $run->result_payload = $resultPayload;

        $this->save($run);
    }

    public function markFailed(string $id, string $errorMessage): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }

        /** @var DemoJobRunResource $run */
        $run->status = 'failed';
        $run->progress_message = $errorMessage;

        $this->save($run);
    }
}
