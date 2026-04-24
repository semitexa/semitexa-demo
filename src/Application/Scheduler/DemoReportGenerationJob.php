<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Demo\Application\Service\DemoReportBuilder;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Domain\Value\ScheduledJobContext;

#[AsScheduledJob(
    key: 'demo.report_generation',
    cronExpression: '*/30 * * * * *',
    overlapPolicy: 'skip',
)]
final class DemoReportGenerationJob implements ScheduledJobInterface
{
    #[InjectAsReadonly]
    protected DemoJobRunRepositoryInterface $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoReportBuilder $reportBuilder;

    public function handle(ScheduledJobContext $context): void
    {
        if (!isset($this->jobRunRepository) || !isset($this->reportBuilder)) {
            return;
        }

        $active = $this->jobRunRepository->findActiveRuns();
        $activeReport = null;
        foreach ($active as $run) {
            if ($run->getJobType() === 'report_generation') {
                $activeReport = $run;
                break;
            }
        }

        if ($activeReport === null) {
            $run = new DemoJobRun();
            $run->setJobType('report_generation');
            $run->setStatus('running');
            $run->setProgressPercent(0);
            $run->setProgressMessage('Starting…');
            $run->setSchedulerRunId($context->runId);
            $run->setAttemptNumber(1);
            $run = $this->jobRunRepository->save($run);
            $activeReport = $run;
        }

        $this->reportBuilder->advanceProgress($activeReport->getId());
    }
}
