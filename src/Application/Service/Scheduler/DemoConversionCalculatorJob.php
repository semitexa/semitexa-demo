<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service\Scheduler;

use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Domain\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Domain\Model\ScheduledJobContext;

#[AsService]
#[AsScheduledJob(
    key: 'demo.conversion_calculator',
    // The six-field (seconds) expression this had never parsed, so the job
    // never ran. Every 15 minutes: each run writes rows nothing prunes.
    cronExpression: '*/15 * * * *',
    overlapPolicy: 'skip',
)]
final class DemoConversionCalculatorJob implements ScheduledJobInterface
{
    #[InjectAsReadonly]
    protected DemoAnalyticsAggregator $aggregator;

    public function handle(ScheduledJobContext $context): void
    {
        if (!isset($this->aggregator)) {
            return;
        }

        $tenantId = $context->payload['tenantId'] ?? 'acme';
        $this->aggregator->recordSnapshot('conversions', $tenantId);
    }
}
