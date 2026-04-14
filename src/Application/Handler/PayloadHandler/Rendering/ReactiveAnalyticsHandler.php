<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveAnalyticsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveAnalyticsSlot;
use Semitexa\Demo\Application\Service\DemoAnalyticsAggregator;
use Semitexa\Demo\Application\Service\DemoCatalogService;
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

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ReactiveAnalyticsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $snapshots = $this->analyticsAggregator->getLatestSnapshots('acme');
        $metricTypes = $this->analyticsAggregator->getMetricTypes();

        $panels = [];
        foreach ($metricTypes as $type) {
            $snapshot = $snapshots[$type] ?? null;
            $value = $snapshot?->getValue() ?? null;
            $label = match ($type) {
                'pageviews'    => 'Page Views',
                'conversions'  => 'Conversion Rate',
                'top_products' => 'Top Products',
                default        => ucfirst($type),
            };
            $display = $value !== null
                ? ($type === 'conversions' ? number_format((float)$value * 100, 2) . '%' : number_format((int)$value))
                : '—';

            $panels[] = [
                'label' => $label,
                'value' => $display,
                'updated' => $snapshot !== null
                    ? 'Last snapshot: ' . ($snapshot->getPeriodEnd()?->format('Y-m-d H:i:s') ?? 'unknown')
                    : 'No data yet',
            ];
        }

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-analytics') ?? [];

        $sourceCode = [
            'ReactiveAnalyticsSlot' => $this->sourceCodeReader->readClassSource(ReactiveAnalyticsSlot::class),
            'DemoAnalyticsAggregator' => $this->sourceCodeReader->readClassSource(DemoAnalyticsAggregator::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Reactive Analytics — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'reactive-analytics',
                'infoWhat' => $explanation['what'] ?? 'A live dashboard can be assembled from independent server snapshots, so panels update progressively without one giant frontend state sync.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('reactive-analytics')
            ->withTitle('Reactive Analytics')
            ->withSummary('Independent analytics jobs can light up one dashboard progressively, while the page stays server-rendered from the first byte.')
            ->withEntryLine('Each panel updates when its own job finishes, so the dashboard feels live without turning into a client-side orchestration layer.')
            ->withHighlights(['multi-job snapshots', 'independent panel refresh', 'refreshInterval: 5', 'SSR-first live UI'])
            ->withLearnMoreLabel('See the dashboard contract →')
            ->withDeepDiveLabel('How multi-job panels stay coherent →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/analytics-panels.html.twig', [
                'eyebrow' => 'Progressive Dashboard',
                'title' => 'Panels wake up as their own server snapshots arrive',
                'summary' => 'Each analytics job owns one slice of truth, and the live slot composes the dashboard from those server snapshots instead of a frontend sync loop.',
                'panels' => $panels,
                'signals' => [
                    ['value' => (string) count($panels), 'label' => 'panels updated independently'],
                    ['value' => '0', 'label' => 'client merge layer required'],
                    ['value' => 'SSR', 'label' => 'rendering model from first byte to live refresh'],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
