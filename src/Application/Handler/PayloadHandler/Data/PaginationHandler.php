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
        $mode = $payload->getMode();
        $limit = $payload->getLimit();
        $page = $payload->getPage();
        $total = $this->productRepository->countAll();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;
        $items = $this->productRepository->findPage($limit, $offset);
        $isCursorMode = $mode === 'cursor';
        $baseQuery = ['limit' => $limit, 'mode' => $mode];

        $rows = '';
        foreach ($items as $product) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>$%.2f</td></tr>',
                htmlspecialchars($product->name),
                $product->price,
            );
        }

        $resultPreview = '<div class="result-preview">'
            . sprintf(
                '<p><strong>%s mode</strong> — page <strong>%d</strong> of <strong>%d</strong>, %d total products, %d per page</p>',
                ucfirst($mode),
                $page,
                $totalPages,
                $total,
                $limit,
            )
            . '<table class="data-table"><thead><tr><th>Name</th><th>Price</th></tr></thead>'
            . '<tbody>' . ($rows ?: '<tr><td colspan="2">No results — seed data first.</td></tr>') . '</tbody>'
            . '</table>'
            . '<div class="pagination-nav">'
            . ($page > 1
                ? sprintf(
                    '<a href="?%s" class="btn btn--secondary">%s</a>',
                    http_build_query($baseQuery + ['page' => $page - 1]),
                    $isCursorMode ? '← Newer' : '← Prev',
                )
                : '<span class="btn btn--disabled">' . ($isCursorMode ? '← Newer' : '← Prev') . '</span>')
            . sprintf(' <span class="page-info">%d / %d</span> ', $page, $totalPages)
            . ($page < $totalPages
                ? sprintf(
                    '<a href="?%s" class="btn btn--secondary">%s</a>',
                    http_build_query($baseQuery + ['page' => $page + 1]),
                    $isCursorMode ? 'Older →' : 'Next →',
                )
                : '<span class="btn btn--disabled">' . ($isCursorMode ? 'Older →' : 'Next →') . '</span>')
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
