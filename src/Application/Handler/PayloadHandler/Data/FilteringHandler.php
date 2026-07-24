<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Data\FilteringPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: FilteringPayload::class, resource: DemoFeatureResource::class)]
final class FilteringHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $productRepository;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(FilteringPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'data',
            slug: 'filtering',
            entryLine: 'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.',
            learnMoreLabel: 'See the filter attributes →',
            deepDiveLabel: 'How filter criteria compile →',
            relatedSlugs: [],
            fallbackTitle: 'Filtering',
            fallbackSummary: 'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.',
            fallbackHighlights: ['#[Filterable]', 'FilterableTrait', 'FilterableResourceInterface', 'getFilterCriteria()'],
            explanation: $this->explanationProvider->getExplanation('data', 'filtering') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $products = $this->applyNameAndCategoryFilters(
            $this->productRepository->findFiltered(
                status: $payload->getStatus(),
                minPrice: $payload->getPriceMin(),
                maxPrice: $payload->getPriceMax(),
                limit: 50,
            ),
            $payload,
        );
        $products = array_slice($products, 0, 10);
        $activeFilters = $this->activeFilters($payload);

        $hasFilters = $payload->getName() !== null
            || $payload->getStatus() !== null
            || $payload->getPriceMin() !== null
            || $payload->getPriceMax() !== null
            || $payload->getCategoryId() !== null;

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Model (Filterable)' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Filter Criteria',
                'title' => 'Live filtered result set',
                'summary' => $activeFilters !== []
                    ? 'Applied filters narrow the dataset before repository fetch.'
                    : ($hasFilters
                        ? 'The requested filters currently resolve to an empty demo subset.'
                        : 'No filters applied — showing the broad demo dataset.'),
                'activeFilters' => $activeFilters,
                'stats' => [
                    ['value' => (string) count($products), 'label' => 'Matched products'],
                    ['value' => (string) count($activeFilters), 'label' => 'Active filters'],
                ],
                'columns' => ['Name', 'Price', 'Status'],
                'rows' => array_map(
                    static fn (DemoProduct $product): array => [
                        ['text' => $product->getName()],
                        ['text' => '$' . number_format((float) $product->getPrice(), 2)],
                        ['text' => $product->getStatus()],
                    ],
                    array_slice($products, 0, 6),
                ),
                'emptyMessage' => 'No results — seed data first.',
            ]);
    }

    /**
     * @param list<DemoProduct> $products
     * @return list<DemoProduct>
     */
    private function applyNameAndCategoryFilters(array $products, FilteringPayload $payload): array
    {
        return array_values(array_filter(
            $products,
            function ($product) use ($payload): bool {
                if (!$product instanceof DemoProduct) {
                    return false;
                }

                $name = $payload->getName();
                if ($name !== null && $name !== '' && stripos($product->getName(), $name) === false) {
                    return false;
                }

                $categoryId = $payload->getCategoryId();
                if ($categoryId !== null && $categoryId !== '' && $product->getCategoryId() !== $categoryId) {
                    return false;
                }

                return true;
            },
        ));
    }

    /**
     * @return list<string>
     */
    private function activeFilters(FilteringPayload $payload): array
    {
        return array_values(array_filter([
            $payload->getName() !== null ? 'name=' . $payload->getName() : null,
            $payload->getStatus() !== null ? 'status=' . $payload->getStatus() : null,
        ]));
    }
}
