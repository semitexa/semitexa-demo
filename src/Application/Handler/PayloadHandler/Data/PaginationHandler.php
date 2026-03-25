<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\PaginationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PaginationPayload::class, resource: DemoFeatureResource::class)]
final class PaginationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(PaginationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $limit = $payload->getLimit();
        $page = $payload->getPage();
        $offset = ($page - 1) * $limit;

        // Fetch all and slice for offset pagination demo
        $all = $this->productRepository->findAll(1000);
        $total = count($all);
        $totalPages = (int) ceil($total / $limit);
        $items = array_slice($all, $offset, $limit);

        $rows = '';
        foreach ($items as $product) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>$%.2f</td></tr>',
                htmlspecialchars($product->name),
                $product->price,
            );
        }

        $resultPreview = '<div class="result-preview">'
            . sprintf('<p>Page <strong>%d</strong> of <strong>%d</strong> — %d total products, %d per page</p>', $page, $totalPages, $total, $limit)
            . '<table class="data-table"><thead><tr><th>Name</th><th>Price</th></tr></thead>'
            . '<tbody>' . ($rows ?: '<tr><td colspan="2">No results — seed data first.</td></tr>') . '</tbody>'
            . '</table>'
            . '<div class="pagination-nav">'
            . ($page > 1 ? sprintf('<a href="?page=%d&limit=%d" class="btn btn--secondary">← Prev</a>', $page - 1, $limit) : '<span class="btn btn--disabled">← Prev</span>')
            . sprintf(' <span class="page-info">%d / %d</span> ', $page, $totalPages)
            . ($page < $totalPages ? sprintf('<a href="?page=%d&limit=%d" class="btn btn--secondary">Next →</a>', $page + 1, $limit) : '<span class="btn btn--disabled">Next →</span>')
            . '</div>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('data', 'pagination') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
        ];

        return $resource
            ->pageTitle('Pagination — Semitexa Demo')
            ->withSection('data')
            ->withSlug('pagination')
            ->withTitle('Pagination')
            ->withSummary('Offset and cursor pagination out of the box — switch modes with a single query parameter.')
            ->withEntryLine('Offset and cursor pagination out of the box — switch modes with a single query parameter.')
            ->withHighlights(['PaginatedResult', 'limit()', 'offset()', 'cursor pagination', 'total count'])
            ->withLearnMoreLabel('See pagination in action →')
            ->withDeepDiveLabel('Offset vs cursor trade-offs →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
