<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
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
    protected DemoJobRunRepository $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoProductImporter $productImporter;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ReactiveImportPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $runs = $this->jobRunRepository->findByJobType('demo_product_import');
        $latestRun = $runs[0] ?? null;

        $totalRows = $this->productImporter->getTotalRows();
        $progress = $latestRun?->progress_percent ?? 0;
        $processed = (int) round($progress / 100 * $totalRows);

        $resultPreview = '<div class="result-preview">'
            . '<p>A product import job ticks every minute — the row counter below animates in real time as batches complete.</p>'
            . '<div class="import-stats">'
            . '<div class="import-stat"><span class="import-stat__value" data-counter data-target="' . $processed . '">' . number_format($processed) . '</span>'
            . '<span class="import-stat__label">rows processed</span></div>'
            . '<div class="import-stat"><span class="import-stat__value">' . number_format($totalRows) . '</span>'
            . '<span class="import-stat__label">total rows</span></div>'
            . '</div>'
            . '<div class="progress-bar" style="margin-top:0.75rem">'
            . '<div class="progress-bar__fill" style="width:' . (int) $progress . '%"></div>'
            . '</div>'
            . '<p class="muted" style="margin-top:0.5rem">' . htmlspecialchars($latestRun?->progress_message ?? 'Waiting for import job…') . '</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-import') ?? [];

        $sourceCode = [
            'ReactiveImportSlot' => $this->sourceCodeReader->readClassSource(ReactiveImportSlot::class),
            'DemoProductImporter' => $this->sourceCodeReader->readClassSource(DemoProductImporter::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Reactive Import — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('reactive-import')
            ->withTitle('Reactive Import')
            ->withSummary('A product import job ticks every minute — the row counter animates in real time.')
            ->withEntryLine('A product import job ticks every minute — the row counter animates in real time.')
            ->withHighlights(['refreshInterval: 2', 'batch processing', 'progress_percent', 'heartbeat tick'])
            ->withLearnMoreLabel('See batch config →')
            ->withDeepDiveLabel('Heartbeat tick internals →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
