<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Service\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Service\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Async\SseStreamPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Demo\Application\Service\DemoSsePreviewContext;

#[AsPayloadHandler(payload: SseStreamPayload::class, resource: DemoFeatureResource::class)]
final class SseStreamHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoSsePreviewContext $ssePreview;

    public function handle(SseStreamPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'events',
            slug: 'sse',
            entryLine: 'This demo now receives real backend-generated SSE messages over one long-lived HTTP connection, not client-side simulated updates.',
            learnMoreLabel: 'See the SSE handler →',
            deepDiveLabel: 'SSE connection lifecycle →',
            relatedSlugs: [],
            fallbackTitle: 'SSE Stream',
            fallbackSummary: 'Real-time server push without WebSockets — connect once and receive real backend events over plain HTTP.',
            fallbackHighlights: ['SseEndpointHandler', 'AsyncResourceSseServer', 'EventSource', 'text/event-stream'],
            explanation: $this->explanationProvider->getExplanation('events', 'sse'),
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Client JS' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Static/js/sse-demo.js'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/sse-stream.html.twig', $this->ssePreview->forPage('/demo/events/sse'));
    }

}
