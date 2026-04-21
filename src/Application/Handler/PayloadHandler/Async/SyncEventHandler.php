<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Event\EventDispatcherInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Handler\DomainListener\DemoItemCreatedListener;
use Semitexa\Demo\Application\Payload\Event\DemoItemCreated;
use Semitexa\Demo\Application\Payload\Request\Async\SyncEventPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SyncEventPayload::class, resource: DemoFeatureResource::class)]
final class SyncEventHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected ?EventDispatcherInterface $eventDispatcher = null;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SyncEventPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $fired = $payload->getTrigger() === 'fire';
        $eventLog = [];

        if ($fired) {
            $event = new DemoItemCreated();
            $event->setItemId('demo-item-' . substr(md5((string) microtime(true)), 0, 8));
            $event->setItemName('Demo Product');
            $event->setSection('events');
            $event->setTimestamp(microtime(true));
            $this->eventDispatcher?->dispatch($event);

            $eventLog = [
                ['event' => 'DemoItemCreated', 'listener' => 'DemoItemCreatedListener', 'mode' => 'Sync', 'status' => 'fired'],
                ['event' => 'DemoNotificationEvent', 'listener' => 'DemoNotificationListener', 'mode' => 'Async', 'status' => 'queued'],
            ];
        }

        $spec = new FeatureSpec(
            section: 'events',
            slug: 'sync',
            entryLine: 'Dispatch an event and all sync listeners run before the response is sent.',
            learnMoreLabel: 'See the event & listener code →',
            deepDiveLabel: 'Dispatcher execution modes →',
            relatedSlugs: [],
            fallbackTitle: 'Sync Events',
            fallbackSummary: 'Dispatch an event and all sync listeners run before the response is sent.',
            fallbackHighlights: ['#[AsEvent]', '#[Propagated]', '#[AsEventListener]', 'EventExecution::Sync', 'EventDispatcherInterface'],
            explanation: $this->explanationProvider->getExplanation('events', 'sync') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Event' => $this->sourceCodeReader->readClassSource(DemoItemCreated::class),
                'Listener' => $this->sourceCodeReader->readClassSource(DemoItemCreatedListener::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Inline Dispatch',
                'title' => 'Fire an event in the current request',
                'summary' => $fired
                    ? 'Event dispatched successfully. The sync listener ran inline before this response was built, and the event is now eligible for ledger persistence when the ledger runtime is enabled.'
                    : 'No event fired yet. Submit the form to dispatch DemoItemCreated.',
                'form' => [
                    'label' => 'Fire DemoItemCreated →',
                    'hidden' => [['name' => 'trigger', 'value' => 'fire']],
                ],
                'columns' => $eventLog !== [] ? ['Event', 'Listener', 'Mode', 'Status'] : [],
                'rows' => array_map(
                    static fn (array $entry): array => [
                        ['text' => $entry['event']],
                        ['text' => $entry['listener']],
                        ['text' => $entry['mode'], 'code' => true],
                        ['text' => $entry['status'], 'variant' => $entry['status'] === 'fired' ? 'success' : 'warning'],
                    ],
                    $eventLog,
                ),
                'emptyMessage' => 'Trigger the event to see the dispatch log.',
                'note' => $fired ? 'The async listener is queued after the response is flushed. With ledger enabled, both demo events are also written to the node ledger.' : null,
            ]);
    }
}
