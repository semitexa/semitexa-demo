<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Reactive;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_reactive_report',
    slot: 'reactive_report',
    template: '@project-layouts-semitexa-demo/reactive/report.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/reactive/report.skeleton.html.twig',
    refreshInterval: 3,
    clientModules: ['@project-static-semitexa-demo/reactive/report-chart.js'],
)]
final class ReactiveReportSlot extends HtmlSlotResponse
{
    public function withStatus(string $status): static { return $this->with('status', $status); }
    public function withProgress(int $percent): static { return $this->with('progress', $percent); }
    public function withMessage(string $message): static { return $this->with('message', $message); }
    public function withChartData(?array $chartData): static { return $this->with('chartData', $chartData); }
}
