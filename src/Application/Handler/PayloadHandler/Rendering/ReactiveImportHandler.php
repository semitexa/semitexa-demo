<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveImportPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveImportSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoProductImporter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveImportPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveImportHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoJobRunRepositoryInterface $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoProductImporter $productImporter;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ReactiveImportPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'reactive-import',
            entryLine: 'The import keeps running on the server, and the page stays honest by streaming fresh HTML instead of faking progress in frontend state.',
            learnMoreLabel: 'See the live import contract →',
            deepDiveLabel: 'How server-owned progress stays live →',
            relatedSlugs: [],
            fallbackTitle: 'Reactive Import',
            fallbackSummary: 'Background batches keep moving, and the page reflects server progress as live HTML instead of a client-managed progress app.',
            fallbackHighlights: ['refreshInterval: 2', 'server-owned progress', 'batch processing', 'SSR-first live UI'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'reactive-import') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $latestRun = $this->jobRunRepository->findByJobType('demo_product_import')[0] ?? null;
        $totalRows = $this->productImporter->getTotalRows();
        $progress = $latestRun?->getProgressPercent() ?? 0;
        $processed = (int) round($progress / 100 * $totalRows);

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'ReactiveImportSlot' => $this->sourceCodeReader->readClassSource(ReactiveImportSlot::class),
                'DemoProductImporter' => $this->sourceCodeReader->readClassSource(DemoProductImporter::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/import-progress.html.twig', [
                'eyebrow' => 'Server-Owned Progress',
                'title' => 'Import progress stays truthful without a frontend state machine',
                'summary' => 'The import job advances on the server, and the live slot keeps re-rendering that authoritative state as HTML.',
                'processed' => number_format($processed),
                'total' => number_format($totalRows),
                'progress' => (int) $progress,
                'message' => $latestRun?->getProgressMessage() ?? 'Waiting for import job…',
                'signals' => [
                    ['value' => '1', 'label' => 'source of truth for progress'],
                    ['value' => '0', 'label' => 'frontend counters to reconcile'],
                    ['value' => 'HTML', 'label' => 'payload pushed back into the page'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Client-side drift',
                        'title' => 'Progress is simulated in frontend state',
                        'summary' => 'The browser invents a temporary truth while the job is still running somewhere else.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'SSR-first live import',
                        'title' => 'Server state is the only state',
                        'summary' => 'Each refresh shows the latest job snapshot directly from the server-owned import pipeline.',
                    ],
                ],
            ]);
    }
}
