<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Scheduler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Scheduler\Attribute\AsScheduledJob;
use Semitexa\Scheduler\Contract\ScheduledJobInterface;
use Semitexa\Scheduler\Domain\Value\ScheduledJobContext;

#[AsScheduledJob(
    key: 'demo.top_products_ranker',
    cronExpression: '*/45 * * * * *',
    overlapPolicy: 'skip',
)]
final class DemoTopProductsRankerJob implements ScheduledJobInterface
{
    #[InjectAsReadonly]
    protected ?DemoAnalyticsAggregator $aggregator = null;

    public function handle(ScheduledJobContext $context): void
    {
        $tenantId = $context->payload['tenantId'] ?? 'acme';
        $this->aggregator?->recordSnapshot('top_products', $tenantId);
    }
}
