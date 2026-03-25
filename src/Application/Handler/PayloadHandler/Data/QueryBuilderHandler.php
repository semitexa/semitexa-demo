<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\QueryBuilderPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
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

    public function handle(QueryBuilderPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $products = $this->productRepository->findAll($payload->getLimit());

        // Apply filters that the query snippet displays
        if ($payload->getStatus() !== null) {
            $products = array_filter($products, static fn($p) => $p->status === $payload->getStatus());
        }
        if ($payload->getMinPrice() !== null) {
            $products = array_filter($products, static fn($p) => (float) $p->price >= $payload->getMinPrice());
        }
        if ($payload->getMaxPrice() !== null) {
            $products = array_filter($products, static fn($p) => (float) $p->price <= $payload->getMaxPrice());
        }
        $products = array_values($products);

        $count = count($products);
        $rows = '';
        foreach (array_slice($products, 0, 5) as $product) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>$%.2f</td><td>%s</td></tr>',
                htmlspecialchars($product->name),
                $product->price,
                htmlspecialchars($product->status),
            );
        }

        $querySnippet = '$products = $this->select()' . "\n"
            . ($payload->getStatus() !== null ? sprintf("    ->where('status', '=', '%s')\n", htmlspecialchars($payload->getStatus())) : '')
            . ($payload->getMinPrice() !== null ? sprintf("    ->where('price', '>=', %.2f)\n", $payload->getMinPrice()) : '')
            . ($payload->getMaxPrice() !== null ? sprintf("    ->where('price', '<=', %.2f)\n", $payload->getMaxPrice()) : '')
            . sprintf("    ->limit(%d)\n", $payload->getLimit())
            . '    ->fetchAll();';

        $resultPreview = '<div class="result-preview">'
            . '<p>Query returned <strong>' . $count . ' products</strong>.</p>'
            . '<pre class="code-inline">' . htmlspecialchars($querySnippet) . '</pre>'
            . '<table class="data-table"><thead><tr><th>Name</th><th>Price</th><th>Status</th></tr></thead>'
            . '<tbody>' . ($rows ?: '<tr><td colspan="3">No results — seed the data first.</td></tr>') . '</tbody>'
            . '</table></div>';

        $explanation = $this->explanationProvider->getExplanation('data', 'query') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
        ];

        return $resource
            ->pageTitle('Query Builder — Semitexa Demo')
            ->withSection('data')
            ->withSlug('query')
            ->withTitle('Query Builder')
            ->withSummary('Compose type-safe queries with a fluent API — no raw SQL, no magic strings.')
            ->withEntryLine('Compose type-safe queries with a fluent API — no raw SQL, no magic strings.')
            ->withHighlights(['SelectQuery', 'where()', 'orderBy()', 'limit()', 'fetchAll()', 'fetchOne()'])
            ->withLearnMoreLabel('See the query builder →')
            ->withDeepDiveLabel('How SelectQuery compiles SQL →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
