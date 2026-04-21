<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\PayloadPartsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PayloadPartsPayload::class, resource: DemoFeatureResource::class)]
final class PayloadPartsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(PayloadPartsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'payload-parts',
            entryLine: 'A payload can stay the single trusted boundary even when multiple modules need to extend it and guard their own fields.',
            learnMoreLabel: 'See modular composition →',
            deepDiveLabel: 'How wrapper composition works →',
            relatedSlugs: [],
            fallbackTitle: 'Payload Parts',
            fallbackSummary: 'One module owns the route, another module can extend the same payload contract without forking or reopening the base class.',
            fallbackHighlights: ['#[AsPayloadPart]', 'trait composition', 'module extension', 'field-level guards'],
            explanation: $this->explanationProvider->getExplanation('routing', 'payload-parts') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Modular Route Contract',
                'title' => 'One payload, more than one module',
                'summary' => 'The route stays declared once, but extra request fields can be composed in by another module through a typed trait instead of a fork.',
                'paragraphs' => [
                    'The base module owns the route path and the main transport contract.',
                    'A second module adds its own request concerns, such as tracking or preview flags, with #[AsPayloadPart] on a trait, and the trait can own the setter-level guards for those fields.',
                    'Module A and Module B handlers now receive the same composed payload instance, so both can read the mixed contract without a payload fork.',
                ],
                'columns' => ['Concern', 'Without payload parts', 'With #[AsPayloadPart]'],
                'rows' => [
                    [
                        ['text' => 'Base route ownership'],
                        ['text' => 'Fork or reopen the original payload class', 'variant' => 'warning'],
                        ['text' => 'Base payload stays untouched', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Module-specific query fields'],
                        ['text' => 'Ad-hoc arrays or handler glue', 'variant' => 'warning'],
                        ['text' => 'Typed trait methods on the same payload', 'variant' => 'success'],
                    ],
                    [
                        ['text' => 'Handler input'],
                        ['text' => 'Scattered conditionals', 'variant' => 'warning'],
                        ['text' => 'One composed payload DTO', 'variant' => 'success'],
                    ],
                ],
                'note' => 'The important part is not the trait itself. The important part is that both modules still work with one payload boundary after composition, while each added field keeps its own normalization and guard logic.',
            ])
            ->withSourceCode([
                'Base Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadParts/BaseSearchPayload.example.php'),
                'Module A Handler' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadParts/ModuleAHandler.example.php'),
                'Module Trait' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadParts/SearchTrackingPart.example.php'),
                'Module B Handler' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PayloadParts/ModuleBHandler.example.php'),
            ]);
    }
}
