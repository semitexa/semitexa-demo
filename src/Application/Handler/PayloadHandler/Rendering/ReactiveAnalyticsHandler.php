<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
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
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoAnalyticsAggregator $analyticsAggregator;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ReactiveAnalyticsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'reactive-analytics',
            entryLine: 'Each panel updates when its own job finishes, so the dashboard feels live without turning into a client-side orchestration layer.',
            learnMoreLabel: 'See the dashboard contract →',
            deepDiveLabel: 'How multi-job panels stay coherent →',
            relatedSlugs: [],
            fallbackTitle: 'Reactive Analytics',
            fallbackSummary: 'Independent analytics jobs can light up one dashboard progressively, while the page stays server-rendered from the first byte.',
            fallbackHighlights: ['multi-job snapshots', 'independent panel refresh', 'refreshInterval: 5', 'SSR-first live UI'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'reactive-analytics') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $panels = $this->buildPanels();

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'ReactiveAnalyticsSlot' => $this->sourceCodeReader->readClassSource(ReactiveAnalyticsSlot::class),
                'DemoAnalyticsAggregator' => $this->sourceCodeReader->readClassSource(DemoAnalyticsAggregator::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
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
            ]);
    }

    /**
     * @return list<array{label: string, value: string, updated: string}>
     */
    private function buildPanels(): array
    {
        $snapshots = $this->analyticsAggregator->getLatestSnapshots('acme');
        $panels = [];

        foreach ($this->analyticsAggregator->getMetricTypes() as $type) {
            $snapshot = $snapshots[$type] ?? null;
            $value = $snapshot?->getValue();

            $panels[] = [
                'label' => match ($type) {
                    'pageviews'    => 'Page Views',
                    'conversions'  => 'Conversion Rate',
                    'top_products' => 'Top Products',
                    default        => ucfirst($type),
                },
                'value' => $value === null
                    ? '—'
                    : ($type === 'conversions'
                        ? number_format((float) $value * 100, 2) . '%'
                        : number_format((int) $value)),
                'updated' => $snapshot !== null
                    ? 'Last snapshot: ' . ($snapshot->getPeriodEnd()?->format('Y-m-d H:i:s') ?? 'unknown')
                    : 'No data yet',
            ];
        }

        return $panels;
    }
}
