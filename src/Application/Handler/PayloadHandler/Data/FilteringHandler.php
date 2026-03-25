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

    public function handle(FilteringPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $filter = new DemoProductResource();

        if ($payload->getName() !== null) {
            $filter->name = $payload->getName();
        }
        if ($payload->getStatus() !== null) {
            $filter->status = $payload->getStatus();
        }

        $hasFilters = $payload->getName() !== null || $payload->getStatus() !== null;
        $products = $hasFilters
            ? $this->productRepository->find($filter)
            : $this->productRepository->findAll(10);

        $count = count($products);
        $rows = '';
        foreach (array_slice($products, 0, 6) as $product) {
            /** @var DemoProductResource $product */
            $rows .= sprintf(
                '<tr><td>%s</td><td>$%.2f</td><td>%s</td></tr>',
                htmlspecialchars($product->name),
                $product->price,
                htmlspecialchars($product->status),
            );
        }

        $activeFilters = array_filter([
            $payload->getName() !== null ? 'name=' . htmlspecialchars($payload->getName()) : null,
            $payload->getStatus() !== null ? 'status=' . htmlspecialchars($payload->getStatus()) : null,
        ]);

        $resultPreview = '<div class="result-preview">'
            . ($activeFilters !== []
                ? '<p>Active filters: <code>' . implode(', ', $activeFilters) . '</code></p>'
                : '<p>No filters applied — showing all products.</p>')
            . sprintf('<p>Found <strong>%d products</strong>.</p>', $count)
            . '<table class="data-table"><thead><tr><th>Name</th><th>Price</th><th>Status</th></tr></thead>'
            . '<tbody>' . ($rows ?: '<tr><td colspan="3">No results — seed data first.</td></tr>') . '</tbody>'
            . '</table></div>';

        $explanation = $this->explanationProvider->getExplanation('data', 'filtering') ?? [];

        $sourceCode = [
            'Model (Filterable)' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Filtering — Semitexa Demo')
            ->withSection('data')
            ->withSlug('filtering')
            ->withTitle('Filtering')
            ->withSummary('Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.')
            ->withEntryLine('Mark a property #[Filterable] and the ORM handles the rest — no manual WHERE clauses.')
            ->withHighlights(['#[Filterable]', 'FilterableTrait', 'FilterableResourceInterface', 'getFilterCriteria()'])
            ->withLearnMoreLabel('See the filter attributes →')
            ->withDeepDiveLabel('How filter criteria compile →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
