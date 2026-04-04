<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Handler\DomainListener\DemoNotificationListener;
use Semitexa\Demo\Application\Payload\Request\Async\DeferredHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredHandlerPayload::class, resource: DemoFeatureResource::class)]
final class DeferredHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DeferredHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('events', 'deferred') ?? [];

        $sourceCode = [
            'Async Listener' => $this->sourceCodeReader->readClassSource(DemoNotificationListener::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Deferred Handler — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'events',
                'currentSlug' => 'deferred',
                'infoWhat' => $explanation['what'] ?? 'Async listeners run after the response via Swoole defer in the same worker.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('events')
            ->withSlug('deferred')
            ->withTitle('Deferred Handler')
            ->withSummary('Heavy work runs after the response is sent — the user gets instant feedback.')
            ->withEntryLine('Heavy work runs after the response is sent — the user gets instant feedback.')
            ->withHighlights(['EventExecution::Async', 'Swoole\\Event::defer()', 'post-response', 'non-blocking'])
            ->withLearnMoreLabel('See the deferred listener →')
            ->withDeepDiveLabel('How Swoole defer works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Post-Response Execution',
                'title' => 'Same worker, later in the lifecycle',
                'summary' => 'Async listeners are scheduled with Swoole defer, so the client gets the response before the listener runs.',
                'columns' => ['Mode', 'When it runs', 'Survives restart', 'Best for'],
                'rows' => [
                    [
                        ['text' => 'Sync', 'code' => true],
                        ['text' => 'Before response'],
                        ['text' => 'N/A'],
                        ['text' => 'Validation, required side-effects'],
                    ],
                    [
                        ['text' => 'Async', 'code' => true],
                        ['text' => 'After response'],
                        ['text' => 'No'],
                        ['text' => 'Email, cache bust, audit log'],
                    ],
                    [
                        ['text' => 'Queued', 'code' => true],
                        ['text' => 'Worker picks up'],
                        ['text' => 'Yes'],
                        ['text' => 'Heavy jobs, retry logic, cross-worker'],
                    ],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
