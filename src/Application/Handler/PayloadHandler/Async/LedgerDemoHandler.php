<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Async;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Event\EventDispatcherInterface;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Handler\DomainListener\DemoItemCreatedListener;
use Semitexa\Demo\Application\Payload\Event\DemoItemCreated;
use Semitexa\Demo\Application\Payload\Request\Async\LedgerDemoPayload;
use Semitexa\Demo\Application\Payload\Session\LedgerDemoSessionSegment;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoLedgerInspector;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: LedgerDemoPayload::class, resource: DemoFeatureResource::class)]
final class LedgerDemoHandler implements TypedHandlerInterface
{
    private const MIN_TRIGGER_INTERVAL_SECONDS = 2.0;

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected EventDispatcherInterface $eventDispatcher;

    #[InjectAsMutable]
    protected SessionInterface $session;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoLedgerInspector $ledgerInspector;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(LedgerDemoPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        if (!isset($this->session)) {
            throw new \RuntimeException('Session service is required for LedgerDemoHandler.');
        }

        /** @var LedgerDemoSessionSegment $segment */
        $segment = $this->session->getPayload(LedgerDemoSessionSegment::class);
        [$statusMessage, $statusVariant] = $this->applyTrigger($payload, $segment);
        $segment->rotateNonce();
        $this->session->setPayload($segment);

        $inspection = $this->ledgerInspector->inspect();

        $spec = new FeatureSpec(
            section: 'events',
            slug: 'ledger',
            entryLine: 'This page is not public: authorization is required, the write action is fixed, and the inspection surface exposes only filtered demo events.',
            learnMoreLabel: 'See the protected ledger flow →',
            deepDiveLabel: 'How the safe ledger inspector works →',
            relatedSlugs: [],
            fallbackTitle: 'Ledger Demo',
            fallbackSummary: 'Dispatch a protected demo event and inspect only the persisted demo ledger rows through a safe read-only view.',
            fallbackHighlights: ['#[Propagated]', '#[RequiresPermission]', 'typed session nonce', 'SQLite read-only view'],
            explanation: $this->explanationProvider->getExplanation('events', 'ledger') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Payload' => $this->sourceCodeReader->readClassSource(LedgerDemoPayload::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Inspector' => $this->sourceCodeReader->readClassSource(DemoLedgerInspector::class),
                'Event' => $this->sourceCodeReader->readClassSource(DemoItemCreated::class),
                'Listener' => $this->sourceCodeReader->readClassSource(DemoItemCreatedListener::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Protected Ledger Surface',
                'title' => 'Append one fixed demo event and inspect only demo rows',
                'summary' => $this->buildSummary($inspection, $statusMessage),
                'paragraphs' => [
                    'The route is guarded with #[RequiresPermission(\'products.read\')], so guests and unauthorized sessions never see the ledger page.',
                    'The POST surface accepts only action=fire plus a session-bound nonce. No arbitrary SQL, replay, event class, or payload input is exposed here.',
                    'The inspector opens the SQLite ledger in read-only mode and filters the view to the demo domain only.',
                ],
                'form' => $inspection['enabled']
                    ? [
                        'label' => 'Append DemoItemCreated →',
                        'hidden' => [
                            ['name' => 'action', 'value' => 'fire'],
                            ['name' => 'nonce', 'value' => $segment->getNonce()],
                        ],
                    ]
                    : null,
                'columns' => ['Signal', 'Value'],
                'rows' => [
                    [['text' => 'Ledger file', 'code' => true], ['text' => $inspection['path'], 'code' => true]],
                    [['text' => 'Runtime state'], ['text' => $inspection['enabled'] ? 'ready' : 'not ready', 'variant' => $inspection['enabled'] ? 'success' : 'warning']],
                    [['text' => 'Read-only filter'], ['text' => 'domain=demo', 'code' => true]],
                    [['text' => 'Last action'], ['text' => $statusMessage ?? 'No write attempted in this request.', 'variant' => $statusVariant]],
                ],
                'note' => $inspection['enabled']
                    ? 'Clicking the button dispatches DemoItemCreated, which also triggers DemoNotificationEvent through the sync listener. Both are eligible for ledger persistence.'
                    : 'Ledger runtime is not fully enabled yet. Configure LEDGER_* and NATS env values, then reload the app to activate real ledger writes.',
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Filtered Demo Rows',
                'title' => 'Recent persisted demo events',
                'summary' => $inspection['error'] === null
                    ? 'Only demo-domain ledger rows are shown here, so this page does not expose unrelated system events.'
                    : 'The ledger file could not be inspected safely in this request.',
                'stats' => $inspection['stats'],
                'columns' => ['Seq', 'Event Type', 'Source', 'Publish', 'Created At'],
                'rows' => $inspection['rows'],
                'emptyMessage' => $inspection['error']
                    ?? 'No demo ledger rows found yet. Trigger the demo action after enabling the ledger runtime.',
            ]);
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function applyTrigger(LedgerDemoPayload $payload, LedgerDemoSessionSegment $segment): array
    {
        if ($segment->getNonce() === null) {
            $segment->issueNonce();
        }

        if ($payload->getAction() !== 'fire') {
            return [null, 'active'];
        }

        if (!$segment->matchesNonce($payload->getNonce())) {
            return ['Rejected: the session nonce is missing or stale.', 'error'];
        }

        if (!$this->ledgerInspector->isEnabled()) {
            return ['Ledger runtime is not enabled in the current environment.', 'warning'];
        }

        if (!isset($this->eventDispatcher)) {
            return ['Event dispatcher is unavailable, so the ledger demo cannot emit the propagated event.', 'error'];
        }

        $lastTriggeredAt = $segment->getLastTriggeredAt();
        if ($lastTriggeredAt !== null && (microtime(true) - (float) $lastTriggeredAt) < self::MIN_TRIGGER_INTERVAL_SECONDS) {
            return ['Write throttled for safety. Wait a moment before sending another demo event.', 'warning'];
        }

        $event = new DemoItemCreated();
        $event->setItemId('ledger-demo-' . substr(bin2hex(random_bytes(8)), 0, 12));
        $event->setItemName('Ledger Demo Item');
        $event->setSection('events');
        $event->setTimestamp(microtime(true));
        $this->eventDispatcher->dispatch($event);

        $segment->markTriggered((string) microtime(true));

        return ['DemoItemCreated dispatched. Refresh rows below to confirm persistence and listener fan-out.', 'success'];
    }

    /**
     * @param array<string, mixed> $inspection
     */
    private function buildSummary(array $inspection, ?string $statusMessage): string
    {
        if ($statusMessage !== null) {
            return $statusMessage;
        }

        if ($inspection['error'] !== null) {
            return 'The protected route loaded, but the ledger file could not be inspected in read-only mode.';
        }

        if (!$inspection['fileExists']) {
            return 'The route is protected and ready, but the ledger SQLite file does not exist yet in this environment.';
        }

        if (!$inspection['enabled']) {
            return 'The ledger file is present, but runtime hooks are not enabled yet, so the demo stays read-only until configuration is complete.';
        }

        return 'Ledger runtime is enabled. Use the button to dispatch one fixed propagated event and inspect the resulting demo-only rows below.';
    }
}
