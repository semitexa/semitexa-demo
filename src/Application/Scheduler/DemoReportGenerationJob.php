<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Domain\Model\DemoJobRun;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
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
    protected ?DemoJobRunRepository $jobRunRepository = null;

    #[InjectAsReadonly]
    protected ?DemoReportBuilder $reportBuilder = null;

    public function handle(ScheduledJobContext $context): void
    {
        if ($this->jobRunRepository === null || $this->reportBuilder === null) {
            return;
        }

        $active = $this->jobRunRepository->findActiveRuns();
        $activeReport = null;
        foreach ($active as $run) {
            if ($run->jobType === 'report_generation') {
                $activeReport = $run;
                break;
            }
        }

        if ($activeReport === null) {
            $run = new DemoJobRun();
            $run->jobType = 'report_generation';
            $run->status = 'running';
            $run->progressPercent = 0;
            $run->progressMessage = 'Starting…';
            $run->schedulerRunId = $context->runId;
            $run->attemptNumber = 1;
            $run = $this->jobRunRepository->save($run);
            $activeReport = $run;
        }

        $this->reportBuilder->advanceProgress($activeReport->id);
    }
}
