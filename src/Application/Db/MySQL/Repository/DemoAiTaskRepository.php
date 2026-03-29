<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoAiTaskRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoAiTaskResource
    {
        /** @var DemoAiTaskResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoAiTaskResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoAiTaskResource> */
        return $this->repository()->query()
            ->where(DemoAiTaskTableModel::column('tenant_id'), Operator::Equals, $tenantId)
            ->limit($limit)
            ->fetchAllAs(DemoAiTaskResource::class, $this->orm()->getMapperRegistry());
    }

    public function findPending(int $limit = 10): array
    {
        /** @var list<DemoAiTaskResource> */
        return $this->repository()->query()
            ->where(DemoAiTaskTableModel::column('status'), Operator::Equals, 'pending')
            ->orderBy(DemoAiTaskTableModel::column('created_at'), Direction::Asc)
            ->limit($limit)
            ->fetchAllAs(DemoAiTaskResource::class, $this->orm()->getMapperRegistry());
    }

    public function findByStatus(string $status): array
    {
        /** @var list<DemoAiTaskResource> */
        return $this->repository()->query()
            ->where(DemoAiTaskTableModel::column('status'), Operator::Equals, $status)
            ->orderBy(DemoAiTaskTableModel::column('created_at'), Direction::Desc)
            ->fetchAllAs(DemoAiTaskResource::class, $this->orm()->getMapperRegistry());
    }

    public function updateStatus(string $id, string $status): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            return false;
        }
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
        $task->stage_results = $stageResultsJson;
        $this->save($task);
        return true;
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoAiTaskTableModel::class, DemoAiTaskResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function copyInto(object $source, DemoAiTaskResource $target): void
    {
        $source instanceof DemoAiTaskResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
