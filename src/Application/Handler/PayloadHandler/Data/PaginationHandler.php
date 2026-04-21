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
use Semitexa\Demo\Application\Payload\Request\Data\PaginationPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PaginationPayload::class, resource: DemoFeatureResource::class)]
final class PaginationHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $productRepository;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(PaginationPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'data',
            slug: 'pagination',
            entryLine: 'Offset and cursor pagination out of the box — switch modes with a single query parameter.',
            learnMoreLabel: 'See pagination in action →',
            deepDiveLabel: 'Offset vs cursor trade-offs →',
            relatedSlugs: [],
            fallbackTitle: 'Pagination',
            fallbackSummary: 'Offset and cursor pagination out of the box — switch modes with a single query parameter.',
            fallbackHighlights: ['PaginatedResult', 'limit()', 'offset()', 'cursor pagination', 'total count'],
            explanation: $this->explanationProvider->getExplanation('data', 'pagination') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $mode = $payload->getMode();
        $limit = $payload->getLimit();
        $total = $this->productRepository->countAll();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($payload->getPage(), $totalPages);
        $items = $this->productRepository->findPage($limit, ($page - 1) * $limit);
        $isCursorMode = $mode === 'cursor';
        $baseQuery = ['limit' => $limit, 'mode' => $mode];

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
                'Repository' => $this->sourceCodeReader->readClassSource(DemoProductRepository::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => ucfirst($mode) . ' pagination',
                'title' => 'Current page window',
                'summary' => sprintf('Page %d of %d with %d total products and %d rows per page.', $page, $totalPages, $total, $limit),
                'stats' => [
                    ['value' => (string) $page, 'label' => 'Current page'],
                    ['value' => (string) $totalPages, 'label' => 'Total pages'],
                    ['value' => (string) $total, 'label' => 'Total rows'],
                ],
                'columns' => ['Name', 'Price'],
                'rows' => array_map(
                    static fn ($p): array => [
                        ['text' => $p->getName()],
                        ['text' => '$' . number_format((float) $p->getPrice(), 2)],
                    ],
                    $items,
                ),
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
            ]);
    }
}
