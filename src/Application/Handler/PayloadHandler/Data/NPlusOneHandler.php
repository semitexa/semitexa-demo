<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Data\NPlusOnePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Hydration\ResourceModelRelationLoader;

#[AsPayloadHandler(payload: NPlusOnePayload::class, resource: DemoFeatureResource::class)]
final class NPlusOneHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(NPlusOnePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('data', 'n-plus-one') ?? [];

        return $resource
            ->pageTitle('N+1 Without Magic — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'n-plus-one',
                'infoWhat' => $explanation['what'] ?? 'Semitexa avoids N+1 by modeling the exact table slice each screen needs instead of leaning on lazy loading.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('n-plus-one')
            ->withTitle('N+1 Without Magic')
            ->withSummary('Semitexa avoids N+1 by using resource slices for the exact columns and relations each screen needs, instead of hiding database traffic behind implicit relation loading.')
            ->withEntryLine('No magic, no lazy loading, no bloated entity graphs. A screen asks for one slice, the ORM hydrates exactly that slice.')
            ->withHighlights(['ResourceModelRelationLoader', 'resource slice', 'no lazy loading', '#[FromTable]', 'batch relations'])
            ->withLearnMoreLabel('Compare the two ORM styles →')
            ->withDeepDiveLabel('How Semitexa avoids N+1 →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/n-plus-one-showcase.html.twig', [
                'painPoints' => [
                    'In other ORMs, fat-entity models often over-fetch columns first, then hide follow-up queries behind property access.',
                    'Implicit relation loading can make local code look simple while database traffic quietly scales with the number of rows on screen.',
                    'Traditionally this is handled with heavier fetch plans, special loading modes, or proxy behavior that becomes hard to reason about.',
                ],
                'stats' => [
                    ['value' => '4', 'label' => 'base columns for a product card'],
                    ['value' => '2', 'label' => 'queries for list + relation batch'],
                    ['value' => '0', 'label' => 'lazy loads hidden in view code'],
                ],
                'plans' => [
                    [
                        'style' => 'Traditional ORM',
                        'variant' => 'warning',
                        'slice' => 'Whole Product entity + proxy relations',
                        'queryPlan' => '1 base query + 12 lazy category/review lookups',
                        'risk' => 'Query count grows with the number of rendered rows.',
                    ],
                    [
                        'style' => 'Semitexa ORM',
                        'variant' => 'active',
                        'slice' => 'ProductCardResource + optional ProductCardWithReviewsResource',
                        'queryPlan' => '1 product slice query + 1 batch relation query',
                        'risk' => 'The fetch plan stays explicit and stable.',
                    ],
                ],
                'columns' => [
                    ['name' => 'id', 'owner' => 'Card slice', 'note' => 'Needed for identity and links.'],
                    ['name' => 'name', 'owner' => 'Card slice', 'note' => 'Rendered on the list card.'],
                    ['name' => 'price', 'owner' => 'Card slice', 'note' => 'Displayed immediately in the UI.'],
                    ['name' => 'category_id', 'owner' => 'Card slice', 'note' => 'Enough to batch-resolve category when needed.'],
                ],
            ])
            ->withL2ContentTemplate('@project-layouts-semitexa-demo/components/previews/n-plus-one-rules.html.twig', [
                'rules' => [
                    'A screen is free to define its own resource abstraction for the same table instead of inheriting one bloated canonical entity.',
                    'The slim slice contains only the columns that the screen actually renders.',
                    'If relations are needed, the ResourceModel relation loader batch-loads them for the whole result set instead of firing lookups row by row.',
                    'Because there is no lazy loading, the fetch plan is visible in the code review and stable in production.',
                ],
            ])
            ->withSourceCode([
                'Fat Entity' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/NPlusOne/FatProductEntity.example.php'),
                'Card Slice' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/NPlusOne/ProductCardResource.example.php'),
                'Card + Reviews' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/NPlusOne/ProductCardWithReviewsResource.example.php'),
                'Relation Loader' => $this->sourceCodeReader->readClassSource(ResourceModelRelationLoader::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withExplanation($explanation);
    }
}
