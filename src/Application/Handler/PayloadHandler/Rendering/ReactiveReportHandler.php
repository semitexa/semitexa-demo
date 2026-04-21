<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Repository\DemoJobRunRepositoryInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\ReactiveReportPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Reactive\ReactiveReportSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoReportBuilder;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ReactiveReportPayload::class, resource: DemoFeatureResource::class)]
final class ReactiveReportHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoJobRunRepositoryInterface $jobRunRepository;

    #[InjectAsReadonly]
    protected DemoReportBuilder $reportBuilder;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ReactiveReportPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'reactive-report',
            entryLine: 'A scheduled job changes server state, and the slot keeps reflecting that state live with no page reload and no client-side state machine.',
            learnMoreLabel: 'See the live-report flow →',
            deepDiveLabel: 'LeaseHeartbeat & retry →',
            relatedSlugs: [],
            fallbackTitle: 'Reactive Report',
            fallbackSummary: 'Background work updates an SSR-first slot in place, so the UI feels live without falling back to SPA state orchestration.',
            fallbackHighlights: ['refreshInterval', '#[AsScheduledJob]', 'DemoJobRun', 'SSR-first live UI'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'reactive-report') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $latestRun = $this->jobRunRepository->findByJobType('report_generation')[0] ?? null;
        $status = $latestRun?->getStatus() ?? 'idle';
        $progress = $latestRun?->getProgressPercent() ?? 0;
        $message = $latestRun?->getProgressMessage() ?? 'Waiting for next scheduled run…';

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'ReactiveReportSlot' => $this->sourceCodeReader->readClassSource(ReactiveReportSlot::class),
                'DemoReportBuilder' => $this->sourceCodeReader->readClassSource(DemoReportBuilder::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
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
            ]);
    }
}
