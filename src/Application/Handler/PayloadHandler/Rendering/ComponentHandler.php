<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Component\CodeBlockComponent;
use Semitexa\Demo\Application\Component\DeepDiveDrawerComponent;
use Semitexa\Demo\Application\Component\DisclosurePromptComponent;
use Semitexa\Demo\Application\Component\ExpandableSectionComponent;
use Semitexa\Demo\Application\Component\ExplanationTooltipComponent;
use Semitexa\Demo\Application\Component\FeatureCardComponent;
use Semitexa\Demo\Application\Component\LiveResultComponent;
use Semitexa\Demo\Application\Payload\Request\Rendering\ComponentPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ComponentPayload::class, resource: DemoFeatureResource::class)]
final class ComponentHandler implements TypedHandlerInterface
{
    private const DEMO_COMPONENTS = [
        ['name' => 'demo-expandable-section', 'class' => ExpandableSectionComponent::class, 'props' => 'targetId, initiallyOpen'],
        ['name' => 'demo-deep-dive-drawer',   'class' => DeepDiveDrawerComponent::class,   'props' => 'targetId'],
        ['name' => 'demo-disclosure-prompt',   'class' => DisclosurePromptComponent::class,  'props' => 'label, variant, target'],
        ['name' => 'demo-explanation-tooltip', 'class' => ExplanationTooltipComponent::class, 'props' => 'term, definition'],
        ['name' => 'demo-feature-card',        'class' => FeatureCardComponent::class,       'props' => 'title, summary, slug, section'],
        ['name' => 'demo-code-block',          'class' => CodeBlockComponent::class,         'props' => 'tabs, featureSlug'],
        ['name' => 'demo-live-result',         'class' => LiveResultComponent::class,        'props' => 'endpoint, method, label, resultId'],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ComponentPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'components') ?? [];

        $sourceCode = [
            'Example Component' => $this->sourceCodeReader->readClassSource(ExpandableSectionComponent::class),
        ];

        return $resource
            ->pageTitle('Components — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'components',
                'infoWhat' => $explanation['what'] ?? 'Attribute-registered components are discovered at boot and rendered as reusable UI primitives.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('components')
            ->withTitle('Components')
            ->withSummary('Reusable, attribute-registered UI components — discovered automatically from the classmap.')
            ->withEntryLine('Reusable, attribute-registered UI components — discovered automatically from the classmap.')
            ->withHighlights(['#[AsComponent]', 'ComponentRegistry', 'props', 'Twig template', 'ClassDiscovery'])
            ->withLearnMoreLabel('See component registration →')
            ->withDeepDiveLabel('How Twig compilation works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Component Registry',
                'title' => sprintf('%d reusable UI components discovered at boot', count(self::DEMO_COMPONENTS)),
                'summary' => 'The demo package registers its UI primitives via #[AsComponent], so no manual registry wiring is needed.',
                'columns' => ['Component name', 'Class', 'Props'],
                'rows' => array_map(
                    static fn (array $component): array => [
                        ['text' => $component['name'], 'code' => true],
                        ['text' => basename(str_replace('\\', '/', $component['class'])), 'code' => true],
                        ['text' => $component['props']],
                    ],
                    self::DEMO_COMPONENTS,
                ),
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
