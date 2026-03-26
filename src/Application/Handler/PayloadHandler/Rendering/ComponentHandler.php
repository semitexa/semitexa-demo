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

    public function handle(ComponentPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $rows = '';
        foreach (self::DEMO_COMPONENTS as $comp) {
            $rows .= sprintf(
                '<tr><td><code>%s</code></td><td><code>%s</code></td><td>%s</td></tr>',
                htmlspecialchars($comp['name']),
                htmlspecialchars(basename(str_replace('\\', '/', $comp['class']))),
                htmlspecialchars($comp['props']),
            );
        }

        $resultPreview = '<div class="result-preview">'
            . '<p>This demo package registers <strong>' . count(self::DEMO_COMPONENTS) . ' components</strong> '
            . 'via <code>#[AsComponent]</code>. All are discovered at boot — no registration code required.</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>Component name</th><th>Class</th><th>Props</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('rendering', 'components') ?? [];

        $sourceCode = [
            'Example Component' => $this->sourceCodeReader->readClassSource(ExpandableSectionComponent::class),
        ];

        return $resource
            ->pageTitle('Components — Semitexa Demo')
            ->withSection('rendering')
            ->withSlug('components')
            ->withTitle('Components')
            ->withSummary('Reusable, attribute-registered UI components — discovered automatically from the classmap.')
            ->withEntryLine('Reusable, attribute-registered UI components — discovered automatically from the classmap.')
            ->withHighlights(['#[AsComponent]', 'ComponentRegistry', 'props', 'Twig template', 'ClassDiscovery'])
            ->withLearnMoreLabel('See component registration →')
            ->withDeepDiveLabel('How Twig compilation works →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
