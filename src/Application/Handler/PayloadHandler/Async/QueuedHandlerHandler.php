<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Async\QueuedHandlerPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: QueuedHandlerPayload::class, resource: DemoFeatureResource::class)]
final class QueuedHandlerHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(QueuedHandlerPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('events', 'queued') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Queued Handler — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'events',
                'currentSlug' => 'queued',
                'infoWhat' => $explanation['what'] ?? 'Queued listeners are serialized into a durable transport and processed by separate workers.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('events')
            ->withSlug('queued')
            ->withTitle('Queued Handler')
            ->withSummary('Events survive restarts and scale across workers — backed by a durable message queue.')
            ->withEntryLine('Events survive restarts and scale across workers — backed by a durable message queue.')
            ->withHighlights(['EventExecution::Queued', 'queue transport', 'NATS', 'retry', 'DLQ'])
            ->withLearnMoreLabel('See the queue configuration →')
            ->withDeepDiveLabel('Queue driver internals →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Durable Transport',
                'title' => 'Push the listener to a queue',
                'summary' => 'Queued listeners survive restarts because the event payload is serialized into the transport instead of staying in worker memory.',
                'codeSnippet' => "#[AsEventListener(\n    event: DemoItemCreated::class,\n    execution: EventExecution::Queued,\n    queue: 'demo.notifications',\n)]\nfinal class DemoNotificationListener\n{\n    public function handle(DemoItemCreated \$event): void { ... }\n}",
                'columns' => ['Feature', 'Supported'],
                'rows' => [
                    [['text' => 'Automatic retry on failure'], ['text' => 'Yes', 'variant' => 'success']],
                    [['text' => 'Dead-letter queue (DLQ)'], ['text' => 'Yes', 'variant' => 'success']],
                    [['text' => 'Cross-worker delivery'], ['text' => 'Yes', 'variant' => 'success']],
                    [['text' => 'Priority queues'], ['text' => 'Yes', 'variant' => 'success']],
                    [['text' => 'Survives worker restart'], ['text' => 'Yes', 'variant' => 'success']],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
