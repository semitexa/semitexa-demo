<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Event\EventDispatcherInterface;
use Semitexa\Demo\Application\Payload\Event\DemoItemCreated;
use Semitexa\Demo\Application\Payload\Request\Async\SyncEventPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SyncEventPayload::class, resource: DemoFeatureResource::class)]
final class SyncEventHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ?EventDispatcherInterface $eventDispatcher = null;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(SyncEventPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $fired = false;
        $eventLog = [];

        if ($payload->getTrigger() === 'fire') {
            $event = new DemoItemCreated();
            $event->setItemId('demo-item-' . substr(md5((string) microtime(true)), 0, 8));
            $event->setItemName('Demo Product');
            $event->setSection('events');
            $event->setTimestamp(microtime(true));

            $this->eventDispatcher?->dispatch($event);
            $fired = true;

            $eventLog = [
                ['event' => 'DemoItemCreated', 'listener' => 'DemoItemCreatedListener', 'mode' => 'Sync', 'status' => 'fired'],
                ['event' => 'DemoNotificationEvent', 'listener' => 'DemoNotificationListener', 'mode' => 'Async', 'status' => 'queued'],
            ];
        }

        $logRows = '';
        foreach ($eventLog as $entry) {
            $logRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td><code>%s</code></td><td><span class="badge badge--%s">%s</span></td></tr>',
                htmlspecialchars($entry['event']),
                htmlspecialchars($entry['listener']),
                htmlspecialchars($entry['mode']),
                $entry['status'] === 'fired' ? 'active' : 'warning',
                htmlspecialchars($entry['status']),
            );
        }

        $resultPreview = '<div class="result-preview">'
            . ($fired
                ? '<p>Event dispatched successfully. The sync listener ran <strong>inline</strong>, '
                  . 'before this response was built. The async listener will run after it is sent.</p>'
                : '<p>No event fired yet.</p>')
            . '<form method="POST"><input type="hidden" name="trigger" value="fire">'
            . '<button type="submit" class="btn btn--primary">Fire DemoItemCreated →</button></form>'
            . ($logRows !== ''
                ? '<table class="data-table" style="margin-top:1rem"><thead><tr><th>Event</th><th>Listener</th><th>Mode</th><th>Status</th></tr></thead><tbody>' . $logRows . '</tbody></table>'
                : '')
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('events', 'sync') ?? [];

        $sourceCode = [
            'Event' => $this->sourceCodeReader->readClassSource(DemoItemCreated::class),
            'Listener' => $this->sourceCodeReader->readClassSource(\Semitexa\Demo\Application\Handler\DomainListener\DemoItemCreatedListener::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Sync Events — Semitexa Demo')
            ->withSection('events')
            ->withSlug('sync')
            ->withTitle('Sync Events')
            ->withSummary('Dispatch an event and all sync listeners run before the response is sent.')
            ->withEntryLine('Dispatch an event and all sync listeners run before the response is sent.')
            ->withHighlights(['#[AsEvent]', '#[AsEventListener]', 'EventExecution::Sync', 'EventDispatcherInterface'])
            ->withLearnMoreLabel('See the event & listener code →')
            ->withDeepDiveLabel('Dispatcher execution modes →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
