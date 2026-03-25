<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Handler\DomainListener\DemoNotificationListener;
use Semitexa\Demo\Application\Payload\Request\Async\DeferredHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredHandlerPayload::class, resource: DemoFeatureResource::class)]
final class DeferredHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(DeferredHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>With <code>EventExecution::Async</code>, the listener is scheduled via '
            . '<code>Swoole\Event::defer()</code>. The response reaches the client first, '
            . 'then the listener runs in the same worker coroutine — zero additional threads.</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>Mode</th><th>When it runs</th><th>Survives restart</th><th>Best for</th></tr></thead>'
            . '<tbody>'
            . '<tr><td><code>Sync</code></td><td>Before response</td><td>N/A</td><td>Validation, side-effects that must complete</td></tr>'
            . '<tr><td><code>Async</code></td><td>After response</td><td>No</td><td>Email, cache bust, audit log</td></tr>'
            . '<tr><td><code>Queued</code></td><td>Worker picks up</td><td>Yes</td><td>Heavy jobs, retry logic, cross-worker</td></tr>'
            . '</tbody></table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('events', 'deferred') ?? [];

        $sourceCode = [
            'Async Listener' => $this->sourceCodeReader->readClassSource(DemoNotificationListener::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Deferred Handler — Semitexa Demo')
            ->withSection('events')
            ->withSlug('deferred')
            ->withTitle('Deferred Handler')
            ->withSummary('Heavy work runs after the response is sent — the user gets instant feedback.')
            ->withEntryLine('Heavy work runs after the response is sent — the user gets instant feedback.')
            ->withHighlights(['EventExecution::Async', 'Swoole\\Event::defer()', 'post-response', 'non-blocking'])
            ->withLearnMoreLabel('See the deferred listener →')
            ->withDeepDiveLabel('How Swoole defer works →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
