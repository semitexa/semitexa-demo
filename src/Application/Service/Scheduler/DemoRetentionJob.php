<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service\Scheduler;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\Config;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Log\StaticLoggerBridge;
use Semitexa\Demo\Domain\Repository\DemoAnalyticsSnapshotRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Domain\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Domain\Model\ScheduledJobContext;

/**
 * The demo's jobs write job-run rows and analytics snapshots every few
 * minutes and nothing pruned them: semitexa.com grew about 600 rows a day.
 * Older than DEMO_RETENTION_DAYS they go, a bounded batch at a time.
 */
#[AsService]
#[AsScheduledJob(
    key: 'demo.retention',
    cronExpression: '17 * * * *',
    overlapPolicy: 'skip',
)]
final class DemoRetentionJob implements ScheduledJobInterface
{
    private const BATCH = 500;
    private const MAX_BATCHES = 20;

    #[InjectAsReadonly]
    protected DemoJobRunRepositoryInterface $jobRuns;

    #[InjectAsReadonly]
    protected DemoAnalyticsSnapshotRepositoryInterface $snapshots;

    #[Config(env: 'DEMO_RETENTION_DAYS', default: 7)]
    protected int $retentionDays = 7;

    public function handle(ScheduledJobContext $context): void
    {
        if ($this->retentionDays <= 0 || !isset($this->jobRuns, $this->snapshots)) {
            return;
        }
        $cutoff = new \DateTimeImmutable("-{$this->retentionDays} days");
        $runs = self::drain(fn (): int => $this->jobRuns->deleteFinishedBefore($cutoff, self::BATCH));
        $snapshots = self::drain(fn (): int => $this->snapshots->deleteEndedBefore($cutoff, self::BATCH));
        if ($runs + $snapshots > 0) {
            StaticLoggerBridge::info('demo', "Demo retention removed {$runs} job runs and {$snapshots} snapshots older than {$this->retentionDays} days.");
        }
    }

    /** @param \Closure(): int $batch */
    private static function drain(\Closure $batch): int
    {
        $total = 0;
        for ($i = 0; $i < self::MAX_BATCHES; $i++) {
            $removed = $batch();
            $total += $removed;
            if ($removed < self::BATCH) {
                break;
            }
        }

        return $total;
    }
}
