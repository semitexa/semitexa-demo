<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\SatisfiesRepositoryContract;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoAiTaskResource;
use Semitexa\Demo\Domain\Model\DemoAiTask;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Query\SystemScopeToken;
use Semitexa\Demo\Domain\Repository\DemoAiTaskRepositoryInterface;
use Semitexa\Orm\Repository\DomainRepository;
use Semitexa\Tenancy\Context\TenantContext;

#[AsRepository]
#[SatisfiesRepositoryContract(of: DemoAiTaskRepositoryInterface::class)]
final class DemoAiTaskRepository implements DemoAiTaskRepositoryInterface
{
    #[InjectAsReadonly]
    protected OrmManager $orm;

    private ?DomainRepository $repository = null;
    private ?SystemScopeToken $systemScopeToken = null;

    public function findById(string $id): ?DemoAiTask
    {
        /** @var DemoAiTask|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoAiTask $entity): DemoAiTask
    {
        /** @var DemoAiTask */
        return $entity->getId() === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    /** @return list<DemoAiTask> */
    public function findByTenant(string $tenantId, int $limit = 100): array
    {
        /** @var list<DemoAiTask> */
        return $this->repository()->query()
            ->where(DemoAiTaskResource::column('tenantId'), Operator::Equals, $tenantId)
            ->limit($limit)
            ->fetchAllAs(DemoAiTask::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoAiTask> */
    public function findPending(int $limit = 10): array
    {
        /** @var list<DemoAiTask> */
        return $this->repository()->query()
            ->where(DemoAiTaskResource::column('status'), Operator::Equals, 'pending')
            ->orderBy(DemoAiTaskResource::column('createdAt'), Direction::Asc)
            ->limit($limit)
            ->fetchAllAs(DemoAiTask::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoAiTask> */
    public function findByStatus(string $status): array
    {
        /** @var list<DemoAiTask> */
        return $this->repository()->query()
            ->where(DemoAiTaskResource::column('status'), Operator::Equals, $status)
            ->orderBy(DemoAiTaskResource::column('createdAt'), Direction::Desc)
            ->fetchAllAs(DemoAiTask::class, $this->orm()->getMapperRegistry());
    }

    public function updateStatus(string $id, string $status): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            return false;
        }
        $task->setStatus($status);
        $this->save($task);
        return true;
    }

    public function updateStageResults(string $id, string $stageResultsJson): bool
    {
        $task = $this->findById($id);
        if ($task === null) {
            return false;
        }
        $task->setStageResults($stageResultsJson);
        $this->save($task);
        return true;
    }

    private function repository(): DomainRepository
    {
        if ($this->repository === null) {
            $this->repository = $this->orm()->repository(DemoAiTaskResource::class, DemoAiTask::class);
        }

        $tenantId = TenantContext::get()?->getTenantId();
        if ($tenantId !== null && $tenantId !== '' && $tenantId !== 'default') {
            return $this->repository->forTenant($tenantId);
        }

        $systemScopeToken = $this->systemScopeToken ??= SystemScopeToken::issue();

        return $this->repository->withoutTenantScope($systemScopeToken);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }
}
