<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;

#[AsService]
final class DemoReportBuilder
{
    private const STAGES = ['querying', 'aggregating', 'formatting', 'complete'];

    #[InjectAsReadonly]
    protected ?DemoJobRunRepositoryInterface $jobRunRepository = null;

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

        $current = $run->getProgressPercent() ?? 0;
        $next = min(100, $current + 25);
        $stageIndex = $next >= 100 ? count(self::STAGES) - 1 : max(0, (int) floor(($next - 1) / 25));
        $stage = self::STAGES[$stageIndex] ?? 'complete';

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
