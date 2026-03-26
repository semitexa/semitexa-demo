<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Reactive;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_reactive_analytics',
    slot: 'reactive_analytics',
    template: '@project-layouts-semitexa-demo/reactive/analytics.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/reactive/analytics.skeleton.html.twig',
    refreshInterval: 5,
    clientModules: ['reactive/analytics-panels.js'],
)]
final class ReactiveAnalyticsSlot extends HtmlSlotResponse
{
    public function withSnapshots(array $snapshots): static { return $this->with('snapshots', $snapshots); }
    public function withMetricTypes(array $types): static { return $this->with('metricTypes', $types); }
}
