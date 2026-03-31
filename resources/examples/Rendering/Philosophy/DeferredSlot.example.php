<?php

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
    public function handle(ProductAnalyticsSlot $slot): ProductAnalyticsSlot
    {
        return $slot->withMetrics(
            $this->analytics->buildProductSnapshot(),
        );
    }
}

// The shell renders immediately. The slow region arrives later as HTML over SSE.
