<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\DeferredScriptInjectionPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredChartWidgetSlot;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: DeferredScriptInjectionPayload::class, resource: DemoFeatureResource::class)]
final class DeferredScriptInjectionHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(DeferredScriptInjectionPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Each deferred block declares its <code>clientModules</code> in the slot attribute. '
            . 'The framework injects each script <strong>exactly once</strong> per page, '
            . 'even if the same block appears multiple times.</p>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "#[AsSlotResource(\n"
                . "    handle: 'demo_deferred_scripts',\n"
                . "    slot: 'deferred_chart_widget',\n"
                . "    template: '...chart-widget.html.twig',\n"
                . "    deferred: true,\n"
                . "    clientModules: ['deferred/chart-widget.js'],\n"
                . ")]"
            )
            . '</pre>'
            . '<p>The module listens for <code>semitexa:block:rendered</code> on the block element '
            . 'and initialises the chart when the content arrives — never before.</p>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'deferred-scripts') ?? [];

        $sourceCode = [
            'Chart Slot' => $this->sourceCodeReader->readClassSource(DeferredChartWidgetSlot::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Script Injection — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('deferred-scripts')
            ->withTitle('Script Injection')
            ->withSummary('Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.')
            ->withEntryLine('Deferred blocks carry their own JS — injected once when the block arrives, never duplicated.')
            ->withHighlights(['clientModules', 'semitexa:block:rendered', 'auto-play', 'script isolation'])
            ->withLearnMoreLabel('See the clientModules pattern →')
            ->withDeepDiveLabel('Block lifecycle events →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
