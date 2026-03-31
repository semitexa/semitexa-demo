<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\ComponentScriptAssetPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ComponentScriptAssetPayload::class, resource: DemoFeatureResource::class)]
final class ComponentScriptAssetHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ComponentScriptAssetPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'component-scripts') ?? [];

        return $resource
            ->pageTitle('Component Script Assets — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'component-scripts',
                'infoWhat' => $explanation['what'] ?? null,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('component-scripts')
            ->withTitle('Component Script Assets')
            ->withSummary('A Semitexa SSR component can own its optional enhancement asset, so behavior travels with the component instead of leaking into page-level glue.')
            ->withEntryLine('No more “remember to include the JS somewhere on this page”. If a component needs optional client enhancement, the contract lives on the component itself.')
            ->withHighlights(['#[AsComponent]', 'script', 'SemitexaComponent.register()', 'auto-require', 'SSR component root'])
            ->withLearnMoreLabel('See the enhancement contract →')
            ->withDeepDiveLabel('Inspect auto-mount behavior →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/component-script-assets.html.twig', [])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/component-script-rules.html.twig', [
                'rules' => [
                    'The script is declared on the component contract, not remembered manually by the page.',
                    'The asset loads only if that component actually rendered on the page.',
                    'The runtime mounts behavior per component root, so one script can safely enhance many instances.',
                    'The script remains optional enhancement, not a second rendering authority.',
                    'The component keeps owning its surface: Twig for HTML, optional script for progressive enhancement.',
                ],
                'checks' => [
                    ['label' => 'script', 'detail' => 'Canonical asset key declared directly in #[AsComponent].'],
                    ['label' => 'auto-require', 'detail' => 'ComponentRenderer requires the runtime and the asset only when the component appears.'],
                    ['label' => 'SemitexaComponent.register()', 'detail' => 'Client behavior registers once and mounts each rendered component root independently.'],
                    ['label' => 'No page glue', 'detail' => 'Feature pages stop manually remembering which component enhancement script to include.'],
                ],
            ])
            ->withSourceCode([
                'Component Contract' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ComponentScripts/ScriptedComponent.example.php'),
                'Twig Usage' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ComponentScripts/ComponentUsage.example.twig'),
                'Component Template' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ComponentScripts/ComponentTemplate.example.twig'),
                'Enhancement Script' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ComponentScripts/ComponentScript.example.js'),
                'Renderer Auto-Require' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Rendering/ComponentScripts/RendererAutoRequire.example.php'),
            ])
            ->withExplanation($explanation);
    }
}
