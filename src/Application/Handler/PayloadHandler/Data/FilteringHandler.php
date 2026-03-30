<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\FilteringPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: FilteringPayload::class, resource: DemoFeatureResource::class)]
final class FilteringHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(FilteringPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $hasFilters = $payload->getName() !== null || $payload->getStatus() !== null;
        $products = $this->productRepository->findFiltered(
            status: $payload->getStatus(),
            limit: 10,
        );

        $count = count($products);
        $rows = [];
        foreach (array_slice($products, 0, 6) as $product) {
            /** @var DemoProductResource $product */
            $rows[] = [
                ['text' => $product->name],
                ['text' => '$' . number_format((float) $product->price, 2)],
                ['text' => (string) $product->status],
            ];
        }

        $activeFilters = array_filter([
            $payload->getName() !== null ? 'name=' . $payload->getName() : null,
            $payload->getStatus() !== null ? 'status=' . $payload->getStatus() : null,
        ]);

        $explanation = $this->explanationProvider->getExplanation('data', 'filtering') ?? [];

        $sourceCode = [
            'Model (Filterable)' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Filtering — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'filtering',
                'infoWhat' => $explanation['what'] ?? 'Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('filtering')
            ->withTitle('Filtering')
            ->withSummary('Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.')
            ->withEntryLine('Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.')
            ->withHighlights(['#[Filterable]', 'FilterableTrait', 'FilterableResourceInterface', 'getFilterCriteria()'])
            ->withLearnMoreLabel('See the filter attributes →')
            ->withDeepDiveLabel('How filter criteria compile →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Filter Criteria',
                'title' => 'Live filtered result set',
                'summary' => $activeFilters !== []
                    ? 'Applied filters narrow the dataset before repository fetch.'
                    : 'No filters applied — showing the broad demo dataset.',
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
