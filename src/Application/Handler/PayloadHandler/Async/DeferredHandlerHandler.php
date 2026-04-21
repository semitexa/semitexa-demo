<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Handler\DomainListener\DemoNotificationListener;
use Semitexa\Demo\Application\Payload\Request\Async\DeferredHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredHandlerPayload::class, resource: DemoFeatureResource::class)]
final class DeferredHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(DeferredHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'events',
            slug: 'deferred',
            entryLine: 'Heavy work runs after the response is sent — the user gets instant feedback.',
            learnMoreLabel: 'See the deferred listener →',
            deepDiveLabel: 'How Swoole defer works →',
            relatedSlugs: [],
            fallbackTitle: 'Deferred Handler',
            fallbackSummary: 'Heavy work runs after the response is sent — the user gets instant feedback.',
            fallbackHighlights: ['EventExecution::Async', 'Swoole\\Event::defer()', 'post-response', 'non-blocking'],
            explanation: $this->explanationProvider->getExplanation('events', 'deferred') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Async Listener' => $this->sourceCodeReader->readClassSource(DemoNotificationListener::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Post-Response Execution',
                'title' => 'Same worker, later in the lifecycle',
                'summary' => 'Async listeners are scheduled with Swoole defer, so the client gets the response before the listener runs.',
                'columns' => ['Mode', 'When it runs', 'Survives restart', 'Best for'],
                'rows' => [
                    [['text' => 'Sync', 'code' => true], ['text' => 'Before response'], ['text' => 'N/A'], ['text' => 'Validation, required side-effects']],
                    [['text' => 'Async', 'code' => true], ['text' => 'After response'], ['text' => 'No'], ['text' => 'Email, cache bust, audit log']],
                    [['text' => 'Queued', 'code' => true], ['text' => 'Worker picks up'], ['text' => 'Yes'], ['text' => 'Heavy jobs, retry logic, cross-worker']],
                ],
            ]);
    }
}
