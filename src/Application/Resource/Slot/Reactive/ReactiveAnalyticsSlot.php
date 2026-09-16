<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Reactive;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlSlotResponse;

/**
 * NOT RENDERED BY ANY PAGE, and that is the point worth writing down.
 *
 * `lint:deferred-slots` reports this slot: it declares `deferred: true` and no
 * template calls `layout_slot_deferred('reactive_analytics')`, so the flag — and the
 * refreshInterval and clientModules beside it — describe a page that does not
 * exist. What the demo page at this feature's route actually renders is a
 * preview component plus this class's SOURCE, as teaching material.
 *
 * So read it as a sample, not as live wiring. Making it live means a slot
 * handler to supply its context and a template call to place it, which is a
 * demo feature rather than a framework fix — filed as tk-slots-nobody-renders.
 */
#[AsSlotResource(
    handle: 'demo_reactive_analytics',
    slot: 'reactive_analytics',
    template: '@project-layouts-semitexa-demo/reactive/analytics.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/reactive/analytics.skeleton.html.twig',
    refreshInterval: 5,
    clientModules: ['@project-static-semitexa-demo/reactive/analytics-panels.js'],
)]
final class ReactiveAnalyticsSlot extends HtmlSlotResponse
{
    public function withSnapshots(array $snapshots): static { return $this->with('snapshots', $snapshots); }
    public function withMetricTypes(array $types): static { return $this->with('metricTypes', $types); }
}
