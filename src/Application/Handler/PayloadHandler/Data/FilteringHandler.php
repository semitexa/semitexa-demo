<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Application\Payload\Request\Data\FilteringPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: FilteringPayload::class, resource: DemoFeatureResource::class)]
final class FilteringHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $productRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    public function handle(FilteringPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'data',
            'filtering',
            'Filtering',
            'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.',
            ['#[Filterable]', 'FilterableTrait', 'FilterableResourceInterface', 'getFilterCriteria()'],
        );
        $explanation = $this->explanationProvider->getExplanation('data', 'filtering') ?? [];

        $hasFilters = $payload->getName() !== null
            || $payload->getStatus() !== null
            || $payload->getPriceMin() !== null
            || $payload->getPriceMax() !== null
            || $payload->getCategoryId() !== null;

        $products = $this->productRepository->findFiltered(
            status: $payload->getStatus(),
            minPrice: $payload->getPriceMin(),
            maxPrice: $payload->getPriceMax(),
            limit: 50,
        );

        $products = array_values(array_filter(
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

        $products = array_slice($products, 0, 10);

        $count = count($products);
        $rows = [];
        foreach (array_slice($products, 0, 6) as $product) {
            /** @var DemoProduct $product */
            $rows[] = [
                ['text' => $product->getName()],
                ['text' => '$' . number_format((float) $product->getPrice(), 2)],
                ['text' => $product->getStatus()],
            ];
        }

        $activeFilters = array_filter([
            $payload->getName() !== null ? 'name=' . $payload->getName() : null,
            $payload->getStatus() !== null ? 'status=' . $payload->getStatus() : null,
        ]);

        $sourceCode = [
            'Model (Filterable)' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'filtering',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('filtering')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the filter attributes →')
            ->withDeepDiveLabel('How filter criteria compile →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Filter Criteria',
                'title' => 'Live filtered result set',
                'summary' => $activeFilters !== []
                    ? 'Applied filters narrow the dataset before repository fetch.'
                    : ($hasFilters
                        ? 'The requested filters currently resolve to an empty demo subset.'
                        : 'No filters applied — showing the broad demo dataset.'),
                'activeFilters' => array_values($activeFilters),
                'stats' => [
                    ['value' => (string) $count, 'label' => 'Matched products'],
                    ['value' => (string) count($activeFilters), 'label' => 'Active filters'],
                ],
                'columns' => ['Name', 'Price', 'Status'],
                'rows' => $rows,
                'emptyMessage' => 'No results — seed data first.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
