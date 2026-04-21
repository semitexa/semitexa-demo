<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Component\DisclosurePromptComponent;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
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
            entryLine: 'Open the component class and you can now see both the rendered UI primitive and the backend event contract it is allowed to trigger.',
            learnMoreLabel: 'See the event bridge in action →',
            deepDiveLabel: 'Inspect the signed manifest flow →',
            relatedSlugs: [],
            fallbackTitle: 'Components',
            fallbackSummary: 'Reusable, attribute-registered UI components — discovered automatically from the classmap.',
            fallbackHighlights: ['#[AsComponent]', 'event', 'triggers', 'component_event_attrs()', 'EventDispatcherInterface'],
            explanation: $this->explanationProvider->getExplanation('rendering', 'components') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Component Class' => $this->sourceCodeReader->readClassSource(DisclosurePromptComponent::class),
                'Component Template' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/View/templates/components/disclosure-prompt.html.twig'),
                'Backend Event' => $this->sourceCodeReader->readClassSource(DemoDisclosureExpanded::class),
                'Event Listener' => $this->sourceCodeReader->readClassSource(DemoDisclosureExpandedListener::class),
                'SSR Bridge Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-ssr/src/Application/Handler/PayloadHandler/ComponentEventDispatchHandler.php'),
                'Frontend Runtime' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-ssr/src/Application/Static/js/component-events.js'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/component-event-bridge.html.twig', []);
    }
}
