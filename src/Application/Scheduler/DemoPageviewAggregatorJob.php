<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Context\ScheduledJobContext;

#[AsScheduledJob(
    key: 'demo.pageview_aggregator',
    cronExpression: '*/15 * * * * *',
    overlapPolicy: 'skip',
)]
final class DemoPageviewAggregatorJob implements ScheduledJobInterface
{
    #[InjectAsReadonly]
    protected ?DemoAnalyticsAggregator $aggregator = null;

    public function handle(ScheduledJobContext $context): void
    {
        $tenantId = $context->payload['tenantId'] ?? 'acme';
        $this->aggregator?->recordSnapshot('pageviews', $tenantId);
    }
}
