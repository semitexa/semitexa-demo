<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\AsService;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;

#[AsService]
final class DemoProductImporter
{
    private const TOTAL_ROWS = 200;
    private const BATCH_SIZE = 40;

    #[InjectAsReadonly]
    protected DemoJobRunRepository $jobRunRepository;

    /**
     * Simulate one batch of CSV import for a given job run.
     */
    public function processBatch(string $jobRunId): void
    {
        $run = $this->jobRunRepository->findById($jobRunId);
        if ($run === null) {
            return;
        }

        $processed = (int) round(($run->progress_percent ?? 0) / 100 * self::TOTAL_ROWS);
        $next = min(self::TOTAL_ROWS, $processed + self::BATCH_SIZE);
        $pct = (int) round(($next / self::TOTAL_ROWS) * 100);

        $this->jobRunRepository->updateProgress(
            $jobRunId,
            $pct,
            sprintf('Importing row %d / %d…', $next, self::TOTAL_ROWS),
        );

        if ($pct >= 100) {
            $this->jobRunRepository->markCompleted($jobRunId, json_encode([
                'rows_processed' => self::TOTAL_ROWS,
                'rows_inserted' => self::TOTAL_ROWS - 3,
                'rows_skipped' => 3,
            ], JSON_THROW_ON_ERROR));
        }
    }

    public function getTotalRows(): int
    {
        return self::TOTAL_ROWS;
    }

    public function getBatchSize(): int
    {
        return self::BATCH_SIZE;
    }
}
