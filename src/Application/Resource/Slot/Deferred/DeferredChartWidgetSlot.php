<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_blocks',
    slot: 'deferred_chart_widget',
    template: '@project-layouts-semitexa-demo/deferred/chart-widget.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/chart-widget.skeleton.html.twig',
    clientModules: ['deferred/chart-widget.js'],
)]
final class DeferredChartWidgetSlot extends HtmlSlotResponse
{
    public function withChartData(array $data): static
    {
        return $this->with('chartData', $data);
    }

    public function withChartType(string $type): static
    {
        return $this->with('chartType', $type);
    }
}
