<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Payload\Request\Data\PaginationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
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

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

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

        $rows = [];
        foreach ($items as $product) {
            $rows[] = [
                ['text' => $product->name],
                ['text' => '$' . number_format((float) $product->price, 2)],
            ];
        }

        $explanation = $this->explanationProvider->getExplanation('data', 'pagination') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
        ];

        return $resource
            ->pageTitle('Pagination — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'pagination',
                'infoWhat' => $explanation['what'] ?? 'Offset and cursor pagination out of the box — switch modes with a single query parameter.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('pagination')
            ->withTitle('Pagination')
            ->withSummary('Offset and cursor pagination out of the box — switch modes with a single query parameter.')
            ->withEntryLine('Offset and cursor pagination out of the box — switch modes with a single query parameter.')
            ->withHighlights(['PaginatedResult', 'limit()', 'offset()', 'cursor pagination', 'total count'])
            ->withLearnMoreLabel('See pagination in action →')
            ->withDeepDiveLabel('Offset vs cursor trade-offs →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => ucfirst($mode) . ' pagination',
                'title' => 'Current page window',
                'summary' => sprintf(
                    'Page %d of %d with %d total products and %d rows per page.',
                    $page,
                    $totalPages,
                    $total,
                    $limit,
                ),
                'stats' => [
                    ['value' => (string) $page, 'label' => 'Current page'],
                    ['value' => (string) $totalPages, 'label' => 'Total pages'],
                    ['value' => (string) $total, 'label' => 'Total rows'],
                ],
                'columns' => ['Name', 'Price'],
                'rows' => $rows,
                'emptyMessage' => 'No results — seed data first.',
                'actions' => [
                    $page > 1 ? [
                        'label' => $isCursorMode ? '← Newer' : '← Prev',
                        'href' => '/demo/data/pagination?' . http_build_query($baseQuery + ['page' => $page - 1]),
                        'variant' => 'secondary',
                    ] : [
                        'label' => $isCursorMode ? '← Newer' : '← Prev',
                    ],
                    $page < $totalPages ? [
                        'label' => $isCursorMode ? 'Older →' : 'Next →',
                        'href' => '/demo/data/pagination?' . http_build_query($baseQuery + ['page' => $page + 1]),
                        'variant' => 'secondary',
                    ] : [
                        'label' => $isCursorMode ? 'Older →' : 'Next →',
                    ],
                ],
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
