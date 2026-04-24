<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
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
    protected DemoJobRunRepositoryInterface $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoProductImporter $importer;

    public function handle(ScheduledJobContext $context): void
    {
        if (!isset($this->jobRunRepository) || !isset($this->importer)) {
            return;
        }

        $active = $this->jobRunRepository->findActiveRuns();
        $activeImport = null;
        foreach ($active as $run) {
            if ($run->getJobType() === 'product_import') {
                $activeImport = $run;
                break;
            }
        }

        if ($activeImport === null) {
            $run = new DemoJobRun();
            $run->setJobType('product_import');
            $run->setStatus('running');
            $run->setProgressPercent(0);
            $run->setProgressMessage('Preparing import…');
            $run->setSchedulerRunId($context->runId);
            $run->setAttemptNumber(1);
            $run = $this->jobRunRepository->save($run);
            $activeImport = $run;
        }

        $this->importer->processBatch($activeImport->getId());
    }
}
