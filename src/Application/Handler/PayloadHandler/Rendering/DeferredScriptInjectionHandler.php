<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredScriptInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredChartWidgetSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredScriptInjectionPayload::class, resource: DemoFeatureResource::class)]
final class DeferredScriptInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(DeferredScriptInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-scripts') ?? [];

        $sourceCode = [
            'Chart Slot' => $this->sourceCodeReader->readClassSource(DeferredChartWidgetSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Script Injection — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'deferred-scripts',
                'infoWhat' => $explanation['what'] ?? 'Deferred slots can declare client modules that are injected once when the block lands.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('deferred-scripts')
            ->withTitle('Script Injection')
            ->withSummary('Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.')
            ->withEntryLine('Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.')
            ->withHighlights(['clientModules', 'semitexa:block:rendered', 'auto-play', 'script isolation'])
            ->withLearnMoreLabel('See the clientModules pattern →')
            ->withDeepDiveLabel('Block lifecycle events →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Client Modules',
                'title' => 'Inject block JS exactly once',
                'summary' => 'Each deferred slot declares its client modules in the attribute, and the framework de-duplicates them across the page.',
                'codeSnippet' => "#[AsSlotResource(\n    handle: 'demo_deferred_scripts',\n    slot: 'deferred_chart_widget',\n    template: '...chart-widget.html.twig',\n    deferred: true,\n    clientModules: ['@project-static-semitexa-demo/deferred/chart-widget.js'],\n)]",
                'note' => 'The module initializes only after semitexa:block:rendered fires for the delivered block.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
