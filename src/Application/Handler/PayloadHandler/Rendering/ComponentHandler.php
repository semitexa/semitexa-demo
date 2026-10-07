<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Component\DisclosurePromptComponent;
use Semitexa\Demo\Application\Service\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Service\Feature\FeatureSpec;
use Semitexa\Demo\Application\Handler\DomainListener\DemoDisclosureExpandedListener;
use Semitexa\Demo\Application\Payload\Event\DemoDisclosureExpanded;
use Semitexa\Demo\Application\Payload\Request\Rendering\ComponentPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ComponentPayload::class, resource: DemoFeatureResource::class)]
final class ComponentHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ComponentPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'rendering',
            slug: 'components',
            entryLine: 'Open the component class and you can see both what it renders and the server method its click reaches.',
            learnMoreLabel: 'See a click reach the server →',
            deepDiveLabel: 'Inspect the signed context flow →',
            relatedSlugs: [],
            fallbackTitle: 'Components',
            fallbackSummary: 'Reusable, attribute-registered UI components, discovered automatically from the classmap.',
            fallbackHighlights: ['#[AsComponent]', '#[UiPart]', '#[UiOn]', 'UiInteractionResult::dispatching()', 'EventDispatcherInterface'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'components'),
            pageTitleSuffix: ' | Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Component Class' => $this->sourceCodeReader->readClassSource(DisclosurePromptComponent::class),
                'Component Template' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/View/templates/components/disclosure-prompt.html.twig'),
                'Backend Event' => $this->sourceCodeReader->readClassSource(DemoDisclosureExpanded::class),
                'Event Listener' => $this->sourceCodeReader->readClassSource(DemoDisclosureExpandedListener::class),
                'Dispatch (on HUG)' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-platform-ui/src/Application/Service/Event/PlatformUiResponseDispatcher.php'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/component-event-bridge.html.twig', []);
    }
}
