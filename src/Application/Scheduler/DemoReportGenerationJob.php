<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoJobRunResource;
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
            if ($run->job_type === 'report_generation') {
                $activeReport = $run;
                break;
            }
        }

        if ($activeReport === null) {
            $run = new DemoJobRunResource();
            $run->job_type = 'report_generation';
            $run->status = 'running';
            $run->progress_percent = 0;
            $run->progress_message = 'Starting…';
            $run->scheduler_run_id = $context->runId;
            $run->attempt_number = 1;
            $this->jobRunRepository->save($run);
            $activeReport = $run;
        }

        $this->reportBuilder->advanceProgress($activeReport->id);
    }
}
