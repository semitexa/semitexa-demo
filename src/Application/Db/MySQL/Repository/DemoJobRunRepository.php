<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\SatisfiesRepositoryContract;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoJobRunResource;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
#[SatisfiesRepositoryContract(of: DemoJobRunRepositoryInterface::class)]
final class DemoJobRunRepository implements DemoJobRunRepositoryInterface
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoJobRun
    {
        /** @var DemoJobRun|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoJobRun $entity): DemoJobRun
    {
        /** @var DemoJobRun */
        return $entity->getId() === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
    }

    /** @return list<DemoJobRun> */
    public function findByJobType(string $jobType): array
    {
        /** @var list<DemoJobRun> */
        return $this->repository()->query()
            ->where(DemoJobRunResource::column('jobType'), Operator::Equals, $jobType)
            ->orderBy(DemoJobRunResource::column('createdAt'), Direction::Desc)
            ->fetchAllAs(DemoJobRun::class, $this->orm()->getMapperRegistry());
    }

    /** @return list<DemoJobRun> */
    public function findActiveRuns(): array
    {
        $rows = $this->adapter()->execute(
            "SELECT * FROM demo_job_runs
             WHERE status IN ('pending', 'running')
             ORDER BY created_at ASC",
            [],
        )->rows;

        return array_map(
            fn (array $row): DemoJobRun => $this->orm()->getMapperRegistry()->mapToDomain(
                $this->orm()->getResourceModelHydrator()->hydrate($row, DemoJobRunResource::class),
                DemoJobRun::class,
            ),
            $rows,
        );
    }

    /** @return list<DemoJobRun> */
    public function findBySchedulerRun(string $schedulerRunId): array
    {
        /** @var list<DemoJobRun> */
        return $this->repository()->query()
            ->where(DemoJobRunResource::column('schedulerRunId'), Operator::Equals, $schedulerRunId)
            ->fetchAllAs(DemoJobRun::class, $this->orm()->getMapperRegistry());
    }

    public function updateProgress(string $id, int $percent, ?string $message = null): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }
        $run->setProgressPercent(max(0, min(100, $percent)));
        if ($message !== null) {
            $run->setProgressMessage($message);
        }
        $this->save($run);
    }

    public function markCompleted(string $id, ?string $resultPayload = null): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }
        $run->setStatus('completed');
        $run->setProgressPercent(100);
        $run->setResultPayload($resultPayload);
        $this->save($run);
    }

    public function markFailed(string $id, string $errorMessage): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }
        $run->setStatus('failed');
        $run->setProgressMessage($errorMessage);
        $this->save($run);
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoJobRunResource::class, DemoJobRun::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function adapter(): \Semitexa\Orm\Adapter\DatabaseAdapterInterface
    {
        return $this->orm()->getAdapter();
    }
}
