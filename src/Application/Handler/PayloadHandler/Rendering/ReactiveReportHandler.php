<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoJobRunRepository;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveReportPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveReportSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoReportBuilder;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveReportPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveReportHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoJobRunRepository $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoReportBuilder $reportBuilder;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ReactiveReportPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $runs = $this->jobRunRepository->findByJobType('report_generation');
        $latestRun = $runs[0] ?? null;

        $status = $latestRun?->status ?? 'idle';
        $progress = $latestRun?->progress_percent ?? 0;
        $message = $latestRun?->progress_message ?? 'Waiting for next scheduled run…';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'reactive-report') ?? [];

        $sourceCode = [
            'ReactiveReportSlot' => $this->sourceCodeReader->readClassSource(ReactiveReportSlot::class),
            'DemoReportBuilder' => $this->sourceCodeReader->readClassSource(DemoReportBuilder::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Reactive Report — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'reactive-report',
                'infoWhat' => $explanation['what'] ?? 'A scheduled report job updates deferred UI state as its progress changes.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('reactive-report')
            ->withTitle('Reactive Report')
            ->withSummary('Background work updates an SSR-first slot in place, so the UI feels live without falling back to SPA state orchestration.')
            ->withEntryLine('A scheduled job changes server state, and the slot keeps reflecting that state live with no page reload and no client-side state machine.')
            ->withHighlights(['refreshInterval', '#[AsScheduledJob]', 'DemoJobRun', 'SSR-first live UI'])
            ->withLearnMoreLabel('See the live-report flow →')
            ->withDeepDiveLabel('LeaseHeartbeat & retry →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ssr-live-ui-showcase.html.twig', [
                'eyebrow' => 'Server State -> Live Slot',
                'title' => 'Scheduled work reflected as live HTML',
                'summary' => 'A background report job changes server state, and the deferred slot keeps re-rendering that state as HTML. The page stays SSR-first from start to finish.',
                'painPoints' => [
                    'Background jobs often force teams to invent a parallel frontend state machine just to show progress.',
                    'Even simple status pages get split into initial SSR and later client-managed rendering logic.',
                    'The result feels live, but the architecture quietly drifts into a small SPA around one widget.',
                ],
                'signals' => [
                    ['value' => ucfirst((string) $status), 'label' => 'current server-side run state'],
                    ['value' => (string) ((int) $progress) . '%', 'label' => 'progress rendered from the slot'],
                    ['value' => '3s', 'label' => 'slot refresh interval'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'State Split',
                        'title' => 'Job progress managed outside SSR',
                        'summary' => 'The page renders once, then a client-side state layer takes over to keep the progress widget alive.',
                        'note' => 'The live bit stops sharing one rendering story with the rest of the page.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'SSR-First Live Report',
                        'title' => 'Server-rendered progress all the way through',
                        'summary' => 'The slot refreshes from real server state and keeps returning HTML, so the page remains conceptually server-rendered.',
                        'note' => $message,
                    ],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/ssr-live-ui-rules.html.twig', [
                'title' => 'Reactive slots without SPA orchestration',
                'summary' => 'Reactive slots take the same deferred-slot model and add timed refresh. The live region still belongs to the server-rendered page, not to a separate frontend app.',
                'rules' => [
                    'The slot starts as SSR output, not as a placeholder for a client-side widget framework.',
                    'Background jobs update storage, and the slot simply keeps re-rendering the current server truth.',
                    'refreshInterval stays declarative on the slot resource instead of hiding in bespoke JavaScript loops.',
                    'The same page can combine static SSR, deferred SSR, and live SSR without changing mental models.',
                ],
                'checks' => [
                    ['label' => 'refreshInterval', 'detail' => 'Controls how often the slot refreshes from the server.'],
                    ['label' => 'DemoJobRun', 'detail' => 'Stores live job state that the slot turns into HTML.'],
                    ['label' => 'ReactiveReportSlot', 'detail' => 'Owns the live region contract separately from the main page resource.'],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
