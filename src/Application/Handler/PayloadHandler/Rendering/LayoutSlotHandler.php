<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Rendering;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Rendering\LayoutSlotPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Resource\Slot\DemoFeatureInfoSlot;
use Semitexa\Demo\Application\Resource\Slot\DemoNavSlot;
use Semitexa\Demo\Application\Resource\Slot\DemoSidebarSlot;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Ssr\Layout\SlotHandlerPipeline;
use Semitexa\Ssr\Layout\SlotRenderer;

#[AsPayloadHandler(payload: LayoutSlotPayload::class, resource: DemoFeatureResource::class)]
final class LayoutSlotHandler implements TypedHandlerInterface
{
    private const DEMO_SLOTS = [
        ['slot' => 'demo_nav',          'class' => DemoNavSlot::class,         'handle' => 'demo', 'desc' => 'Top navigation as a standalone resource'],
        ['slot' => 'demo_sidebar',      'class' => DemoSidebarSlot::class,     'handle' => 'demo', 'desc' => 'Feature tree with its own handler pipeline'],
        ['slot' => 'demo_feature_info', 'class' => DemoFeatureInfoSlot::class, 'handle' => 'demo', 'desc' => 'Field-notes panel rendered independently'],
    ];

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(LayoutSlotPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('rendering', 'slots') ?? [];

        return $resource
            ->pageTitle('Slot Resources — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'rendering',
                'currentSlug' => 'slots',
                'infoWhat' => $explanation['what'] ?? 'Slot resources turn page regions into first-class response pipelines instead of informal partial includes.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('rendering')
            ->withSlug('slots')
            ->withTitle('Slot Resources')
            ->withSummary('Each page region is its own resource pipeline with the same template system as the main page — no scattered partial glue, no mystery wiring.')
            ->withEntryLine('A slot is not a fragment hack. It is a real resource with its own handler pipeline, template, and lifecycle.')
            ->withHighlights(['#[AsSlotResource]', 'HtmlSlotResponse', 'layout_slot()', 'SlotHandlerPipeline', 'shared Twig'])
            ->withLearnMoreLabel('See the slot pipeline →')
            ->withDeepDiveLabel('Why unified templates matter →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/slot-resource-showcase.html.twig', [
                'painPoints' => [
                    'Traditional page composition often leaves nav, sidebar, widgets, and footers as special-case includes with hidden data dependencies.',
                    'Frontend and backend fragments drift apart because each region gets wired by a different mechanism.',
                    'When regions are not first-class resources, no one can tell where their data really comes from or how they are refreshed.',
                ],
                'signals' => [
                    ['value' => '3', 'label' => 'live slot resources in this shell'],
                    ['value' => '1', 'label' => 'shared Twig system for page and slots'],
                    ['value' => '0', 'label' => 'mystery data glue between regions'],
                ],
                'compare' => [
                    [
                        'variant' => 'warning',
                        'eyebrow' => 'Fragment Sprawl',
                        'title' => 'Includes and ad hoc context passing',
                        'summary' => 'Page regions are stitched together manually, and each fragment quietly depends on whatever data happened to be pushed into the template.',
                        'note' => 'The composition works, but nobody can see one explicit pipeline for each region.',
                    ],
                    [
                        'variant' => 'active',
                        'eyebrow' => 'First-Class Page Regions',
                        'title' => 'Slot resource pipeline',
                        'summary' => 'Each region is a resource with its own handler flow, render context, asset collection, and optional deferred/live lifecycle.',
                        'note' => 'The same template system renders the page, the sidebar, the nav, and reactive slots. One mental model, everywhere.',
                    ],
                ],
                'rows' => array_map(
                    static fn (array $slot): array => [
                        ['text' => $slot['slot'], 'code' => true],
                        ['text' => basename(str_replace('\\', '/', $slot['class'])), 'code' => true],
                        ['text' => $slot['desc']],
                        ['text' => sprintf("{{ layout_slot('%s') }}", $slot['slot']), 'code' => true],
                    ],
                    self::DEMO_SLOTS,
                ),
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/slot-resource-rules.html.twig', [
                'rules' => [
                    'A slot is a real response object, not a string include or a magical template fragment.',
                    'Slots use the same Twig system and rendering model as the page itself, so frontend and backend stop speaking different dialects.',
                    'Each region can evolve independently: own handler, own template, own assets, own deferred or reactive lifecycle.',
                    'layout_slot() composes the shell declaratively while the data flow stays explicit and reviewable.',
                ],
                'checks' => [
                    ['label' => '#[AsSlotResource]', 'detail' => 'Registers a named page region as a first-class resource for one layout handle.'],
                    ['label' => 'SlotHandlerPipeline', 'detail' => 'Runs the region through typed slot handlers before it renders.'],
                    ['label' => 'Shared Twig', 'detail' => 'The page template and the slot template use the same rendering engine and conventions.'],
                ],
            ])
            ->withSourceCode([
                'Fragment Sprawl Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Rendering/SlotResources/LegacyDashboardTemplate.example.twig'),
                'Slot Resource' => $this->sourceCodeReader->readClassSource(DemoNavSlot::class),
                'Slot Renderer' => $this->sourceCodeReader->readClassSource(SlotRenderer::class),
                'Slot Handler Pipeline' => $this->sourceCodeReader->readClassSource(SlotHandlerPipeline::class),
                'Slot Resource Example' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Rendering/SlotResources/DashboardSidebarSlot.example.php'),
                'Slot-Aware Layout' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/src/Application/Examples/Rendering/SlotResources/DashboardLayout.example.twig'),
                'Feature Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withExplanation($explanation);
    }
}
