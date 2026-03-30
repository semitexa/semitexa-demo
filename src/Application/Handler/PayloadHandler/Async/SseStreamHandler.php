<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Async\SseStreamPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SseStreamPayload::class, resource: DemoFeatureResource::class)]
final class SseStreamHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(SseStreamPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('events', 'sse') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Client JS' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Static/js/sse-demo.js'),
        ];

        return $resource
            ->pageTitle('SSE Stream — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'events',
                'currentSlug' => 'sse',
                'infoWhat' => $explanation['what'] ?? 'Real-time server push without WebSockets — a persistent HTTP connection that streams events.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('events')
            ->withSlug('sse')
            ->withTitle('SSE Stream')
            ->withSummary('Real-time server push without WebSockets — connect once and let the backend stream named events into the page.')
            ->withEntryLine('This demo now receives real backend-generated SSE messages over one long-lived HTTP connection, not client-side simulated updates.')
            ->withHighlights(['SseEndpointHandler', 'AsyncResourceSseServer', 'EventSource', 'text/event-stream'])
            ->withLearnMoreLabel('See the SSE handler →')
            ->withDeepDiveLabel('SSE connection lifecycle →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/sse-stream.html.twig')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
