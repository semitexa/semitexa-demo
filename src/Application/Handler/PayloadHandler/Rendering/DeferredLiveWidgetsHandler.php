<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredLiveWidgetsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredNotificationSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredLiveWidgetsPayload::class, resource: DemoFeatureResource::class)]
final class DeferredLiveWidgetsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(DeferredLiveWidgetsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>The notification bell below has <code>refreshInterval: 5</code>. '
            . 'Every 5 seconds it re-fetches its slot endpoint and swaps the HTML — '
            . 'the counter updates without any custom JavaScript.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[AsSlotResource(\n"
                . "    handle: 'demo_deferred_live',\n"
                . "    slot: 'deferred_notification',\n"
                . "    deferred: true,\n"
                . "    refreshInterval: 5,  // re-fetch every 5 seconds\n"
                . "    clientModules: ['deferred/notification-bell.js'],\n"
                . ")]"
            )
            . '</pre>'
            . '<p class="note">If the SSE connection drops, the framework reconnects with exponential backoff — '
            . 'the widget keeps updating automatically.</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-live') ?? [];

        $sourceCode = [
            'Notification Slot' => $this->sourceCodeReader->readClassSource(DeferredNotificationSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Live Widgets — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('deferred-live')
            ->withTitle('Live Widgets')
            ->withSummary('Set refreshInterval and the block re-fetches its slot on a timer — live UI with zero JS.')
            ->withEntryLine('Set refreshInterval and the block re-fetches its slot on a timer — live UI with zero JS.')
            ->withHighlights(['refreshInterval', 'auto-refresh', 'SSE reconnection', 'live counter'])
            ->withLearnMoreLabel('See the refreshInterval config →')
            ->withDeepDiveLabel('SSE reconnection strategy →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
