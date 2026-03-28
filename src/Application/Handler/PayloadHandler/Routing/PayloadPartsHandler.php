<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\PayloadPartsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PayloadPartsPayload::class, resource: DemoFeatureResource::class)]
final class PayloadPartsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(PayloadPartsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'payload-parts') ?? [];

        $sourceCode = [
            'Base Payload' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadParts/BaseSearchPayload.example.php'),
            'Module A Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadParts/ModuleAHandler.example.php'),
            'Module Trait' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadParts/SearchTrackingPart.example.php'),
            'Module B Handler' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Routing/PayloadParts/ModuleBHandler.example.php'),
        ];

        return $resource
            ->pageTitle('Payload Parts — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'payload-parts',
                'infoWhat' => $explanation['what'] ?? 'A base payload can be extended by another module without reopening the original route class.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('payload-parts')
            ->withTitle('Payload Parts')
            ->withSummary('One module owns the route, another module can extend the same payload contract without forking or reopening the base class.')
            ->withEntryLine('A payload can stay the single trusted boundary even when multiple modules need to extend it.')
            ->withHighlights(['#[AsPayloadPart]', 'trait composition', 'module extension', 'one payload boundary'])
            ->withLearnMoreLabel('See modular composition →')
            ->withDeepDiveLabel('How wrapper composition works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/concept-preview.html.twig', [
                'eyebrow' => 'Modular Route Contract',
                'title' => 'One payload, more than one module',
                'summary' => 'The route stays declared once, but extra request fields can be composed in by another module through a typed trait instead of a fork.',
                'paragraphs' => [
                    'The base module owns the route path and the main transport contract.',
                    'A second module adds its own request concerns, such as tracking or preview flags, with #[AsPayloadPart] on a trait.',
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
                'note' => 'The important part is not the trait itself. The important part is that both modules still work with one payload boundary after composition.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
