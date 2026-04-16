<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\QueryBuilderPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Demo\Domain\Model\DemoProduct;

#[AsPayloadHandler(payload: QueryBuilderPayload::class, resource: DemoFeatureResource::class)]
final class QueryBuilderHandler implements TypedHandlerInterface
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

    public function handle(QueryBuilderPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'data',
            'query',
            'Query Builder',
            'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.',
            ['ResourceModelQuery', 'where()', 'orderBy()', 'limit()', 'fetchAll()', 'fetchOne()'],
        );
        $explanation = $this->explanationProvider->getExplanation('data', 'query') ?? [];

        $products = $this->productRepository->findFiltered(
            status: $payload->getStatus(),
            minPrice: $payload->getMinPrice(),
            maxPrice: $payload->getMaxPrice(),
            orderBy: $payload->getOrderBy(),
            limit: $payload->getLimit(),
        );

        $count = count($products);
        $rows = [];
        foreach (array_slice($products, 0, 5) as $product) {
            $rows[] = [
                ['text' => $product->getName()],
                ['text' => '$' . number_format((float) $product->getPrice(), 2)],
                ['text' => $product->getStatus()],
            ];
        }

        $querySnippet = '$products = $orm->repository(DemoProductResource::class, DemoProduct::class)' . "\n"
            . '    ->query()' . "\n"
            . ($payload->getStatus() !== null ? sprintf("    ->where(DemoProductResource::column('status'), Operator::Equals, '%s')\n", $payload->getStatus()) : '')
            . ($payload->getMinPrice() !== null ? sprintf("    ->where(DemoProductResource::column('price'), Operator::GreaterThanOrEquals, %.2f)\n", $payload->getMinPrice()) : '')
            . ($payload->getMaxPrice() !== null ? sprintf("    ->where(DemoProductResource::column('price'), Operator::LessThanOrEquals, %.2f)\n", $payload->getMaxPrice()) : '')
            . ($payload->getOrderBy() !== null ? sprintf("    ->orderBy(DemoProductResource::column('%s'), Direction::Asc)\n", $payload->getOrderBy()) : '')
            . sprintf("    ->limit(%d)\n", $payload->getLimit())
            . '    ->fetchAllAs(DemoProduct::class, $orm->getMapperRegistry());';

        $sourceCode = [
            'Catalog Repository' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/QueryBuilder/ProductReadRepository.example.php'),
            'Admin Repository' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/QueryBuilder/ProductAdminRepository.example.php'),
            'Injected Builder' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/QueryBuilder/ProductQueryBuilder.example.php'),
            'Real Demo Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'query',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('query')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Compose type-safe queries with a fluent API — no raw SQL, no magic strings.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the query builder →')
            ->withDeepDiveLabel('How ResourceModelQuery compiles SQL →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Query Builder',
                'title' => 'Compiled query example',
                'summary' => 'The repository composes fluent constraints with typed column references, then materializes a filtered collection.',
                'stats' => [
                    ['value' => (string) $count, 'label' => 'Returned rows'],
                    ['value' => (string) $payload->getLimit(), 'label' => 'Limit'],
                ],
                'codeSnippet' => $querySnippet,
                'columns' => ['Name', 'Price', 'Status'],
                'rows' => $rows,
                'emptyMessage' => 'No results — seed the data first.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
