<?php

declare(strict_types=1);

use App\Service\AnalyticsService;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Ssr\Attributes\AsSlotHandler;
use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'product_page',
    slot: 'analytics_panel',
    deferred: true,
    skeletonTemplate: '@shop/skeletons/analytics-panel.html.twig',
)]
final class ProductAnalyticsSlot extends HtmlSlotResponse
{
    public function withMetrics(array $metrics): self
    {
        return $this->with('metrics', $metrics);
    }
}

#[AsSlotHandler(slot: ProductAnalyticsSlot::class)]
final class ProductAnalyticsSlotHandler
{
    #[InjectAsReadonly]
    protected AnalyticsService $analytics;

    public function handle(ProductAnalyticsSlot $slot): ProductAnalyticsSlot
    {
        return $slot->withMetrics(
            $this->analytics->buildProductSnapshot(),
        );
    }
}

// The shell renders immediately. The slow region arrives later as HTML over SSE.
