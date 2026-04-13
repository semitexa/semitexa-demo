<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
use Semitexa\Demo\Application\Service\DemoProductImporter;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Domain\Value\ScheduledJobContext;

#[AsScheduledJob(
    key: 'demo.product_import',
    cronExpression: '* * * * *',
    overlapPolicy: 'skip',
)]
final class DemoProductImportJob implements ScheduledJobInterface
{
    #[InjectAsReadonly]
    protected ?DemoJobRunRepository $jobRunRepository = null;

    #[InjectAsReadonly]
    protected ?DemoProductImporter $importer = null;

    public function handle(ScheduledJobContext $context): void
    {
        if ($this->jobRunRepository === null || $this->importer === null) {
            return;
        }

        $active = $this->jobRunRepository->findActiveRuns();
        $activeImport = null;
        foreach ($active as $run) {
            if ($run->jobType === 'product_import') {
                $activeImport = $run;
                break;
            }
        }

        if ($activeImport === null) {
            $run = new DemoJobRun();
            $run->jobType = 'product_import';
            $run->status = 'running';
            $run->progressPercent = 0;
            $run->progressMessage = 'Preparing import…';
            $run->schedulerRunId = $context->runId;
            $run->attemptNumber = 1;
            $run = $this->jobRunRepository->save($run);
            $activeImport = $run;
        }

        $this->importer->processBatch($activeImport->id);
    }
}
