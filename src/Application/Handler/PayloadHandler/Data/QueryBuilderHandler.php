<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Data\QueryBuilderPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: QueryBuilderPayload::class, resource: DemoFeatureResource::class)]
final class QueryBuilderHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $productRepository;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(QueryBuilderPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'data',
            slug: 'query',
            entryLine: 'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.',
            learnMoreLabel: 'See the query builder →',
            deepDiveLabel: 'How ResourceModelQuery compiles SQL →',
            relatedSlugs: [],
            fallbackTitle: 'Query Builder',
            fallbackSummary: 'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.',
            fallbackHighlights: ['ResourceModelQuery', 'where()', 'orderBy()', 'limit()', 'fetchAll()', 'fetchOne()'],
            explanation: $this->explanationProvider->getExplanation('data', 'query') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $products = $this->productRepository->findFiltered(
            status: $payload->getStatus(),
            minPrice: $payload->getMinPrice(),
            maxPrice: $payload->getMaxPrice(),
            orderBy: $payload->getOrderBy(),
            limit: $payload->getLimit(),
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Catalog Repository' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/QueryBuilder/ProductReadRepository.example.php'),
                'Admin Repository' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/QueryBuilder/ProductAdminRepository.example.php'),
                'Injected Builder' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Orm/QueryBuilder/ProductQueryBuilder.example.php'),
                'Real Demo Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Query Builder',
                'title' => 'Compiled query example',
                'summary' => 'The repository composes fluent constraints with typed column references, then materializes a filtered collection.',
                'stats' => [
                    ['value' => (string) count($products), 'label' => 'Returned rows'],
                    ['value' => (string) $payload->getLimit(), 'label' => 'Limit'],
                ],
                'codeSnippet' => $this->buildQuerySnippet($payload),
                'columns' => ['Name', 'Price', 'Status'],
                'rows' => array_map(
                    static fn ($product): array => [
                        ['text' => $product->getName()],
                        ['text' => '$' . number_format((float) $product->getPrice(), 2)],
                        ['text' => $product->getStatus()],
                    ],
                    array_slice($products, 0, 5),
                ),
                'emptyMessage' => 'No results — seed the data first.',
            ]);
    }

    private function buildQuerySnippet(QueryBuilderPayload $payload): string
    {
        $lines = ['$products = $orm->repository(DemoProductResource::class, DemoProduct::class)', '    ->query()'];

        if ($payload->getStatus() !== null) {
            $lines[] = sprintf("    ->where(DemoProductResource::column('status'), Operator::Equals, '%s')", $payload->getStatus());
        }
        if ($payload->getMinPrice() !== null) {
            $lines[] = sprintf("    ->where(DemoProductResource::column('price'), Operator::GreaterThanOrEquals, %.2f)", $payload->getMinPrice());
        }
        if ($payload->getMaxPrice() !== null) {
            $lines[] = sprintf("    ->where(DemoProductResource::column('price'), Operator::LessThanOrEquals, %.2f)", $payload->getMaxPrice());
        }
        if ($payload->getOrderBy() !== null) {
            $lines[] = sprintf("    ->orderBy(DemoProductResource::column('%s'), Direction::Asc)", $payload->getOrderBy());
        }
        $lines[] = sprintf("    ->limit(%d)", $payload->getLimit());
        $lines[] = '    ->fetchAllAs(DemoProduct::class, $orm->getMapperRegistry());';

        return implode("\n", $lines);
    }
}
