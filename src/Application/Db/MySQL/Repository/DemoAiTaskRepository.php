<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskResource;
use Semitexa\Orm\Repository\AbstractRepository;

#[AsService]
final class DemoAiTaskRepository extends AbstractRepository
{
    protected function getResourceClass(): string
    {
        return DemoAiTaskResource::class;
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        return $this->select()
            ->where('tenant_id', '=', $tenantId)
            ->limit($limit)
            ->fetchAll();
    }

    public function findPending(int $limit = 10): array
    {
        return $this->select()
            ->where('status', '=', 'pending')
            ->orderBy('created_at', 'ASC')
            ->limit($limit)
            ->fetchAll();
    }

    public function findByStatus(string $status): array
    {
        return $this->select()
            ->where('status', '=', $status)
            ->orderBy('created_at', 'DESC')
            ->fetchAll();
    }

    public function updateStatus(string $id, string $status): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            return false;
        }

        /** @var DemoAiTaskResource $task */
        $task->status = $status;

        $this->save($task);

        return true;
    }

    public function updateStageResults(string $id, string $stageResultsJson): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            return false;
        }

        /** @var DemoAiTaskResource $task */
        $task->stage_results = $stageResultsJson;

        $this->save($task);

        return true;
    }
}
