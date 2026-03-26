<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Attributes\AsService;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;

#[AsService]
final class DemoReportBuilder
{
    private const STAGES = ['querying', 'aggregating', 'formatting', 'complete'];

    #[InjectAsReadonly]
    protected ?DemoJobRunRepository $jobRunRepository = null;

    /**
     * Simulate report aggregation progress for a given job run.
     * Updates progress_percent and progress_message in the DB.
     */
    public function advanceProgress(string $jobRunId): void
    {
        $run = $this->jobRunRepository?->findById($jobRunId);
        if ($run === null) {
            return;
        }

        $current = $run->progress_percent ?? 0;
        $next = min(100, $current + 25);
        $stage = self::STAGES[(int) floor($next / 25)] ?? 'complete';

        $this->jobRunRepository?->updateProgress($jobRunId, $next, ucfirst($stage) . '…');

        if ($next >= 100) {
            $this->jobRunRepository?->markCompleted($jobRunId, json_encode([
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                'values' => [420, 380, 510, 460, 530],
                'currency' => 'USD',
            ], JSON_THROW_ON_ERROR));
        }
    }

    /**
     * Get a simulated chart dataset for a completed report.
     *
     * @return array{labels: list<string>, values: list<int>}
     */
    public function buildChartData(): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            'values' => [420, 380, 510, 460, 530],
        ];
    }
}
