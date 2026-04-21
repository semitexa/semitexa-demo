<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredScriptInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredChartWidgetSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredScriptInjectionPayload::class, resource: DemoFeatureResource::class)]
final class DeferredScriptInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(DeferredScriptInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'deferred-scripts',
            entryLine: 'Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.',
            learnMoreLabel: 'See the clientModules pattern →',
            deepDiveLabel: 'Block lifecycle events →',
            relatedSlugs: [],
            fallbackTitle: 'Script Injection',
            fallbackSummary: 'Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.',
            fallbackHighlights: ['clientModules', 'semitexa:block:rendered', 'auto-play', 'script isolation'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'deferred-scripts') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Chart Slot' => $this->sourceCodeReader->readClassSource(DeferredChartWidgetSlot::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Client Modules',
                'title' => 'Inject block JS exactly once',
                'summary' => 'Each deferred slot declares its client modules in the attribute, and the framework de-duplicates them across the page.',
                'codeSnippet' => "#[AsSlotResource(\n    handle: 'demo_deferred_scripts',\n    slot: 'deferred_chart_widget',\n    template: '...chart-widget.html.twig',\n    deferred: true,\n    clientModules: ['@project-static-semitexa-demo/deferred/chart-widget.js'],\n)]",
                'note' => 'The module initializes only after semitexa:block:rendered fires for the delivered block.',
            ]);
    }
}
