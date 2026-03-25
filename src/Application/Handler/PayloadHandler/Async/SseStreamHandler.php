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
        $resultPreview = '<div class="result-preview" id="sse-demo">'
            . '<p>SSE establishes a persistent HTTP connection using <code>text/event-stream</code>. '
            . 'The server pushes named events; the browser handles them with <code>EventSource</code>.</p>'
            . '<div class="sse-live-panel">'
            . '<div class="sse-status" id="sse-status"><span class="badge badge--warning">Disconnected</span></div>'
            . '<ul class="sse-event-log" id="sse-event-log"><li class="sse-placeholder">Events will appear here…</li></ul>'
            . '<div class="sse-controls">'
            . '<button class="btn btn--primary" id="sse-connect">Connect to SSE stream →</button>'
            . '<button class="btn btn--secondary" id="sse-disconnect" style="display:none">Disconnect</button>'
            . '</div>'
            . '</div>'
            . '</div>';

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
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
