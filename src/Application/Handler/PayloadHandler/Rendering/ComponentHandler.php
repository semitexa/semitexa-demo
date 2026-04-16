<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Component\DisclosurePromptComponent;
use Semitexa\Demo\Application\Handler\DomainListener\DemoDisclosureExpandedListener;
use Semitexa\Demo\Application\Payload\Event\DemoDisclosureExpanded;
use Semitexa\Demo\Application\Payload\Request\Rendering\ComponentPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ComponentPayload::class, resource: DemoFeatureResource::class)]
final class ComponentHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ComponentPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'rendering',
            'components',
            'Components',
            'Reusable, attribute-registered UI components — discovered automatically from the classmap.',
            ['#[AsComponent]', 'event', 'triggers', 'component_event_attrs()', 'EventDispatcherInterface'],
        );
        $explanation = $this->explanationProvider->getExplanation('rendering', 'components') ?? [];

        $sourceCode = [
            'Component Class' => $this->sourceCodeReader->readClassSource(DisclosurePromptComponent::class),
            'Component Template' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/View/templates/components/disclosure-prompt.html.twig'),
            'Backend Event' => $this->sourceCodeReader->readClassSource(DemoDisclosureExpanded::class),
            'Event Listener' => $this->sourceCodeReader->readClassSource(DemoDisclosureExpandedListener::class),
            'SSR Bridge Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-ssr/src/Application/Handler/PayloadHandler/ComponentEventDispatchHandler.php'),
            'Frontend Runtime' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-ssr/src/Application/Static/js/component-events.js'),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'components',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('components')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Open the component class and you can now see both the rendered UI primitive and the backend event contract it is allowed to trigger.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the event bridge in action →')
            ->withDeepDiveLabel('Inspect the signed manifest flow →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/component-event-bridge.html.twig', [])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
