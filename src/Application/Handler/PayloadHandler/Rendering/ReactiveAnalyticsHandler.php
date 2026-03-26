<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveAnalyticsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveAnalyticsSlot;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveAnalyticsPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveAnalyticsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoAnalyticsAggregator $analyticsAggregator;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ReactiveAnalyticsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $snapshots = $this->analyticsAggregator->getLatestSnapshots('acme');
        $metricTypes = $this->analyticsAggregator->getMetricTypes();

        $panels = '';
        foreach ($metricTypes as $type) {
            $snapshot = $snapshots[$type] ?? null;
            $value = $snapshot?->value ?? null;
            $label = match ($type) {
                'pageviews'    => 'Page Views',
                'conversions'  => 'Conversion Rate',
                'top_products' => 'Top Products',
                default        => ucfirst($type),
            };
            $display = $value !== null
                ? ($type === 'conversions' ? number_format((float)$value * 100, 2) . '%' : number_format((int)$value))
                : '—';

            $panels .= '<div class="analytics-panel" data-analytics-panel data-metric-type="' . htmlspecialchars($type) . '">'
                . '<div class="analytics-panel__label">' . htmlspecialchars($label) . '</div>'
                . '<div class="analytics-panel__value" data-panel-value data-raw-value="' . htmlspecialchars((string)$value) . '">'
                . htmlspecialchars($display)
                . '</div>'
                . '<div class="analytics-panel__updated">' . ($snapshot !== null ? 'Last snapshot: ' . htmlspecialchars($snapshot->period_end?->format('Y-m-d H:i:s') ?? 'unknown') : 'No data yet') . '</div>'
                . '</div>';
        }

        $resultPreview = '<div class="result-preview">'
            . '<p>Three cron jobs write snapshots independently — each panel fills in as its job completes.</p>'
            . '<div class="analytics-panels">' . $panels . '</div>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-analytics') ?? [];

        $sourceCode = [
            'ReactiveAnalyticsSlot' => $this->sourceCodeReader->readClassSource(ReactiveAnalyticsSlot::class),
            'DemoAnalyticsAggregator' => $this->sourceCodeReader->readClassSource(DemoAnalyticsAggregator::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Reactive Analytics — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('reactive-analytics')
            ->withTitle('Reactive Analytics')
            ->withSummary('Three cron jobs write snapshots — three panels fill in independently as each job completes.')
            ->withEntryLine('Three cron jobs write snapshots — three panels fill in independently as each job completes.')
            ->withHighlights(['multi-job', 'DemoAnalyticsSnapshot', 'refreshInterval: 5', 'panel orchestration'])
            ->withLearnMoreLabel('See panel config →')
            ->withDeepDiveLabel('Multi-job orchestration →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
