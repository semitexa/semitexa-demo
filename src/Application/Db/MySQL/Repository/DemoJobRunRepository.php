<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Db\MySQL\Repository;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoJobRunResource;
use Semitexa\Demo\Application\Db\MySQL\Table\DemoJobRunTableModel;
use Semitexa\Orm\Attribute\AsRepository;
use Semitexa\Orm\OrmManager;
use Semitexa\Orm\Query\Direction;
use Semitexa\Orm\Query\Operator;
use Semitexa\Orm\Repository\DomainRepository;

#[AsRepository]
final class DemoJobRunRepository
{
    #[InjectAsReadonly]
    protected ?OrmManager $orm = null;

    private ?DomainRepository $repository = null;

    public function findById(string $id): ?DemoJobRunResource
    {
        /** @var DemoJobRunResource|null */
        return $this->repository()->findById($id);
    }

    public function save(DemoJobRunResource $entity): void
    {
        $persisted = $entity->id === '' ? $this->repository()->insert($entity) : $this->repository()->update($entity);
        $this->copyInto($persisted, $entity);
    }

    public function findByJobType(string $jobType): array
    {
        /** @var list<DemoJobRunResource> */
        return $this->repository()->query()
            ->where(DemoJobRunTableModel::column('job_type'), Operator::Equals, $jobType)
            ->orderBy(DemoJobRunTableModel::column('created_at'), Direction::Desc)
            ->fetchAllAs(DemoJobRunResource::class, $this->orm()->getMapperRegistry());
    }

    public function findActiveRuns(): array
    {
        $rows = $this->adapter()->execute(
            "SELECT * FROM demo_job_runs
             WHERE status IN ('pending', 'running')
             ORDER BY created_at ASC",
            [],
        )->rows;

        return array_map(
            fn (array $row): DemoJobRunResource => $this->orm()->getMapperRegistry()->mapToDomain(
                $this->orm()->getTableModelHydrator()->hydrate($row, DemoJobRunTableModel::class),
                DemoJobRunResource::class,
            ),
            $rows,
        );
    }

    public function findBySchedulerRun(string $schedulerRunId): array
    {
        /** @var list<DemoJobRunResource> */
        return $this->repository()->query()
            ->where(DemoJobRunTableModel::column('scheduler_run_id'), Operator::Equals, $schedulerRunId)
            ->fetchAllAs(DemoJobRunResource::class, $this->orm()->getMapperRegistry());
    }

    public function updateProgress(string $id, int $percent, ?string $message = null): void
    {
        $run = $this->findById($id);
        if ($run === null) {
            return;
        }
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
        $run->status = 'failed';
        $run->progress_message = $errorMessage;
        $this->save($run);
    }

    private function repository(): DomainRepository
    {
        return $this->repository ??= $this->orm()->repository(DemoJobRunTableModel::class, DemoJobRunResource::class);
    }

    private function orm(): OrmManager
    {
        return $this->orm ??= new OrmManager();
    }

    private function adapter(): \Semitexa\Orm\Adapter\DatabaseAdapterInterface
    {
        return $this->orm()->getAdapter();
    }

    private function copyInto(object $source, DemoJobRunResource $target): void
    {
        $source instanceof DemoJobRunResource || throw new \InvalidArgumentException('Unexpected persisted resource.');
        foreach (get_object_vars($source) as $property => $value) {
            $target->{$property} = $value;
        }
    }
}
