<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredLiveWidgetsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredNotificationSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredLiveWidgetsPayload::class, resource: DemoFeatureResource::class)]
final class DeferredLiveWidgetsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DeferredLiveWidgetsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'rendering',
            'deferred-live',
            'Live Widgets',
            'A live slot can refresh itself on a timer while the page stays SSR-first — no SPA runtime and no handwritten polling layer.',
            ['refreshInterval', 'auto-refresh', 'SSE reconnection', 'SSR-first live UI'],
        );
        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-live') ?? [];

        $sourceCode = [
            'Notification Slot' => $this->sourceCodeReader->readClassSource(DeferredNotificationSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'deferred-live',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('deferred-live')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Set refreshInterval and the server keeps re-rendering the widget for you. Live UI without converting the page into an app shell.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the live-slot contract →')
            ->withDeepDiveLabel('SSE reconnection strategy →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/ssr-live-ui-showcase.html.twig', [
                'eyebrow' => 'Reactive Slot Refresh',
                'title' => 'Live widgets without a client-side data loop',
                'summary' => 'The server keeps re-rendering the slot as HTML, and the page swaps it in place. The widget feels live, but the UI model stays SSR-first.',
                'painPoints' => [
                    'Live UI often pushes teams toward a separate client-side state system for even simple status widgets.',
                    'Polling code and reconnection logic usually leak into ad hoc JavaScript instead of staying part of the rendering model.',
                    'The shell and the live widget can drift into two different architectures even though they belong to one page.',
                ],
                'signals' => [
                    ['value' => '5s', 'label' => 'refresh interval declared on the slot'],
                    ['value' => '1', 'label' => 'rendering model for static and live regions'],
                    ['value' => '0', 'label' => 'custom polling loop to maintain'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Client Loop Creep',
                        'title' => 'Widget becomes a mini frontend app',
                        'summary' => 'State fetches, retry logic, and rendering drift into custom client code for what should be one live region.',
                        'note' => 'The page stops having one rendering story.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'SSR-First Live Region',
                        'title' => 'Slot keeps itself fresh',
                        'summary' => 'refreshInterval stays on the slot contract, and the framework handles reconnection and HTML replacement.',
                        'note' => 'Live behavior feels native without turning the page into a SPA shell.',
                    ],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
