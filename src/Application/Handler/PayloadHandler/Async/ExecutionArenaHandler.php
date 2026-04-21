<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Environment;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Handler\DomainListener\DemoExecutionShowcaseAsyncListener;
use Semitexa\Demo\Application\Handler\DomainListener\DemoExecutionShowcaseQueuedListener;
use Semitexa\Demo\Application\Handler\DomainListener\DemoExecutionShowcaseSyncListener;
use Semitexa\Demo\Application\Payload\Event\DemoExecutionShowcaseRequested;
use Semitexa\Demo\Application\Payload\Request\Async\ExecutionArenaPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExecutionShowcaseService;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ExecutionArenaPayload::class, resource: DemoFeatureResource::class)]
final class ExecutionArenaHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExecutionShowcaseService $showcaseService;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ExecutionArenaPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'events',
            slug: 'arena',
            entryLine: 'Press any lane. The arena measures request timing, streams backend milestones, and makes the execution model impossible to misread.',
            learnMoreLabel: 'See the execution arena code →',
            deepDiveLabel: 'Why this proves the async model →',
            relatedSlugs: [],
            fallbackTitle: 'Execution Arena',
            fallbackSummary: 'Launch the same backend intent in sync, Swoole async, and queued modes, then watch the proof arrive over SSE.',
            fallbackHighlights: ['EventExecution::Sync', 'EventExecution::Async', 'EventExecution::Queued', 'SSE proof stream'],
            explanation: [
                'what' => 'One browser action emits one backend event. Three listeners with different execution modes turn that same intent into three visibly different response lifecycles.',
                'how' => 'The page opens one SSE session, launches a mode-specific event, and then records proof from both sides: response timing from the launch request and stage-by-stage backend confirmations from the SSE stream.',
                'why' => 'This removes hand-wavy “Semitexa supports async” claims. The sync lane visibly blocks, the Swoole lane returns early and completes later, and the queued lane waits for a worker before finishing.',
                'keywords' => [
                    ['term' => 'EventExecution::Sync', 'definition' => 'Runs the listener inline before the HTTP response is finished.'],
                    ['term' => 'EventExecution::Async', 'definition' => 'Defers the listener with Swoole so the request can finish first.'],
                    ['term' => 'EventExecution::Queued', 'definition' => 'Serializes the listener work into a transport for a queue worker to consume later.'],
                    ['term' => 'SSE proof stream', 'definition' => 'A dedicated EventSource connection that receives backend stage confirmations in real time.'],
                ],
            ],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withRelatedPayloads([
                ['label' => 'Sync Events', 'href' => '/demo/events/sync'],
                ['label' => 'Deferred Handler', 'href' => '/demo/events/deferred'],
                ['label' => 'Queued Handler', 'href' => '/demo/events/queued'],
                ['label' => 'SSE Stream', 'href' => '/demo/events/sse'],
            ])
            ->withSourceCode([
                'Event' => $this->sourceCodeReader->readClassSource(DemoExecutionShowcaseRequested::class),
                'Sync Listener' => $this->sourceCodeReader->readClassSource(DemoExecutionShowcaseSyncListener::class),
                'Async Listener' => $this->sourceCodeReader->readClassSource(DemoExecutionShowcaseAsyncListener::class),
                'Queued Listener' => $this->sourceCodeReader->readClassSource(DemoExecutionShowcaseQueuedListener::class),
                'Client JS' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Static/js/execution-arena.js'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/execution-arena.html.twig', [
                'launchEndpoint' => '/demo/events/arena/launch',
                'sseEndpoint' => Environment::getEnvValue('SSE_ENDPOINT', '/sse'),
                'lanes' => $this->showcaseService->getArenaLanes(),
                'queueCommand' => 'bin/semitexa queue:work nats async',
            ]);
    }
}
