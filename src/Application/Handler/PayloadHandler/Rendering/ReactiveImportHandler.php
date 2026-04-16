<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveImportPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveImportSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoProductImporter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveImportPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveImportHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoJobRunRepositoryInterface $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoProductImporter $productImporter;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ReactiveImportPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $runs = $this->jobRunRepository->findByJobType('demo_product_import');
        $latestRun = $runs[0] ?? null;

        $totalRows = $this->productImporter->getTotalRows();
        $progress = $latestRun?->getProgressPercent() ?? 0;
        $processed = (int) round($progress / 100 * $totalRows);

        $presentation = $this->documents->resolve(
            'rendering',
            'reactive-import',
            'Reactive Import',
            'Background batches keep moving, and the page reflects server progress as live HTML instead of a client-managed progress app.',
            ['refreshInterval: 2', 'server-owned progress', 'batch processing', 'SSR-first live UI'],
        );
        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-import') ?? [];

        $sourceCode = [
            'ReactiveImportSlot' => $this->sourceCodeReader->readClassSource(ReactiveImportSlot::class),
            'DemoProductImporter' => $this->sourceCodeReader->readClassSource(DemoProductImporter::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'reactive-import',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('reactive-import')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('The import keeps running on the server, and the page stays honest by streaming fresh HTML instead of faking progress in frontend state.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the live import contract →')
            ->withDeepDiveLabel('How server-owned progress stays live →')
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
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
