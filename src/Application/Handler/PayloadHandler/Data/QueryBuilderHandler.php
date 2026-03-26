<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\QueryBuilderPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: QueryBuilderPayload::class, resource: DemoFeatureResource::class)]
final class QueryBuilderHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(QueryBuilderPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
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
                ['text' => $product->name],
                ['text' => '$' . number_format((float) $product->price, 2)],
                ['text' => (string) $product->status],
            ];
        }

        $querySnippet = '$products = $this->select()' . "\n"
            . ($payload->getStatus() !== null ? sprintf("    ->where('status', '=', '%s')\n", htmlspecialchars($payload->getStatus())) : '')
            . ($payload->getMinPrice() !== null ? sprintf("    ->where('price', '>=', %.2f)\n", $payload->getMinPrice()) : '')
            . ($payload->getMaxPrice() !== null ? sprintf("    ->where('price', '<=', %.2f)\n", $payload->getMaxPrice()) : '')
            . ($payload->getOrderBy() !== null ? sprintf("    ->orderBy('%s', 'ASC')\n", htmlspecialchars($payload->getOrderBy())) : '')
            . sprintf("    ->limit(%d)\n", $payload->getLimit())
            . '    ->fetchAll();';

        $explanation = $this->explanationProvider->getExplanation('data', 'query') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
        ];

        return $resource
            ->pageTitle('Query Builder — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'query',
                'infoWhat' => $explanation['what'] ?? 'Compose type-safe queries with a fluent API — no raw SQL, no magic strings.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('query')
            ->withTitle('Query Builder')
            ->withSummary('Compose type-safe queries with a fluent API — no raw SQL, no magic strings.')
            ->withEntryLine('Compose type-safe queries with a fluent API — no raw SQL, no magic strings.')
            ->withHighlights(['SelectQuery', 'where()', 'orderBy()', 'limit()', 'fetchAll()', 'fetchOne()'])
            ->withLearnMoreLabel('See the query builder →')
            ->withDeepDiveLabel('How SelectQuery compiles SQL →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Query Builder',
                'title' => 'Compiled query example',
                'summary' => 'The repository composes fluent constraints, then materializes a filtered collection.',
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
