<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Async\QueuedHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: QueuedHandlerPayload::class, resource: DemoFeatureResource::class)]
final class QueuedHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(QueuedHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Queued events are serialised and pushed to a message queue (e.g. RabbitMQ). '
            . 'A separate consumer worker processes them independently — survives app restarts.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[AsEventListener(\n"
                . "    event: DemoItemCreated::class,\n"
                . "    execution: EventExecution::Queued,\n"
                . "    queue: 'demo.notifications',\n"
                . ")]\n"
                . "final class DemoNotificationListener\n"
                . "{\n"
                . "    public function handle(DemoItemCreated \$event): void { ... }\n"
                . "}"
            )
            . '</pre>'
            . '<table class="data-table" style="margin-top:1rem">'
            . '<thead><tr><th>Feature</th><th>Supported</th></tr></thead>'
            . '<tbody>'
            . '<tr><td>Automatic retry on failure</td><td>✓</td></tr>'
            . '<tr><td>Dead-letter queue (DLQ)</td><td>✓</td></tr>'
            . '<tr><td>Cross-worker delivery</td><td>✓</td></tr>'
            . '<tr><td>Priority queues</td><td>✓</td></tr>'
            . '<tr><td>Survives worker restart</td><td>✓</td></tr>'
            . '</tbody></table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('events', 'queued') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Queued Handler — Semitexa Demo')
            ->withSection('events')
            ->withSlug('queued')
            ->withTitle('Queued Handler')
            ->withSummary('Events survive restarts and scale across workers — backed by a durable message queue.')
            ->withEntryLine('Events survive restarts and scale across workers — backed by a durable message queue.')
            ->withHighlights(['EventExecution::Queued', 'queue transport', 'RabbitMQ', 'retry', 'DLQ'])
            ->withLearnMoreLabel('See the queue configuration →')
            ->withDeepDiveLabel('Queue driver internals →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
