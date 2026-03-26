<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Async\SseStreamPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SseStreamPayload::class, resource: DemoFeatureResource::class)]
final class SseStreamHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(SseStreamPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('events', 'sse') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Client JS' => '// See: js/sse-demo.js',
        ];

        return $resource
            ->pageTitle('SSE Stream — Semitexa Demo')
            ->withSection('events')
            ->withSlug('sse')
            ->withTitle('SSE Stream')
            ->withSummary('Real-time server push without WebSockets — a persistent HTTP connection that streams events.')
            ->withEntryLine('Real-time server push without WebSockets — a persistent HTTP connection that streams events.')
            ->withHighlights(['SseEndpointHandler', 'AsyncResourceSseServer', 'EventSource', 'text/event-stream'])
            ->withLearnMoreLabel('See the SSE handler →')
            ->withDeepDiveLabel('SSE connection lifecycle →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/sse-stream.html.twig')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
