<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoCategoryRepository;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoReviewRepository;
use Semitexa\Demo\Application\Payload\Request\Data\RelationsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Orm\Adapter\DatabaseAdapterInterface;

#[AsPayloadHandler(payload: RelationsPayload::class, resource: DemoFeatureResource::class)]
final class RelationsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoCategoryRepository $categoryRepository;

    #[InjectAsReadonly]
    protected DemoProductRepository $productRepository;

    #[InjectAsReadonly]
    protected DemoReviewRepository $reviewRepository;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DatabaseAdapterInterface $db;

    public function handle(RelationsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $categories = array_slice($this->categoryRepository->findAllOrdered(), 0, 3);
        $products = $this->productRepository->findPage(3);
        $categoryRows = [];
        $categorySnapshots = [];
        foreach ($categories as $category) {
            $snapshot = $this->fetchCategorySnapshot($category);
            $categorySnapshots[$category->slug] = $snapshot;

            $categoryRows[] = [
                ['text' => (string) ($category->name ?? 'Unnamed category')],
                ['text' => sprintf('%d products', $snapshot['product_count'])],
                ['text' => $snapshot['sample_products'] !== [] ? implode(', ', $snapshot['sample_products']) : 'No linked products yet'],
            ];
        }

        $productRows = [];
        foreach ($products as $product) {
            $reviewCount = $this->fetchReviewCount($product->name);
            $categoryName = $this->resolveCategoryName($product, $categories);

            $productRows[] = [
                ['text' => (string) ($product->name ?? 'Unnamed product')],
                ['text' => $categoryName],
                ['text' => sprintf('%d reviews', $reviewCount)],
            ];
        }

        $explanation = $this->explanationProvider->getExplanation('data', 'relations') ?? [];

        $sourceCode = [
            'Product Model' => $this->sourceCodeReader->readClassSource(DemoProductResource::class),
            'Category Model' => $this->sourceCodeReader->readClassSource(DemoCategoryResource::class),
        ];

        return $resource
            ->pageTitle('Relations — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'relations',
                'infoWhat' => $explanation['what'] ?? 'Declare associations with attributes — eager loading, N+1 prevention, and nested reads.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('relations')
            ->withTitle('Relations')
            ->withSummary('Declare associations with attributes — eager loading, N+1 prevention, and nested reads.')
            ->withEntryLine('Declare associations with attributes — eager loading, N+1 prevention, and nested reads.')
            ->withHighlights(['#[HasMany]', '#[BelongsTo]', 'CascadeSaver', 'CascadeDeleter', 'eager loading'])
            ->withLearnMoreLabel('See the relation attributes →')
            ->withDeepDiveLabel('How eager loading works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Relation Graph',
                'title' => 'Categories → products',
                'summary' => 'The ORM eager-loads child collections in one pass, so the preview shows real linked records instead of manual joins.',
                'stats' => [
                    ['value' => (string) count($categories), 'label' => 'Loaded categories'],
                    ['value' => (string) array_sum(array_map(
                        fn (DemoCategoryResource $category): int => $categorySnapshots[$category->slug]['product_count'] ?? 0,
                        $categories,
                    )), 'label' => 'Resolved products'],
                ],
                'columns' => ['Category', 'Linked records', 'Sample products'],
                'rows' => $categoryRows,
                'emptyMessage' => 'No relation data yet — seed the demo first.',
                'actions' => [
                    ['label' => 'Products → category + reviews'],
                ],
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/relation-details.html.twig', [
                'eyebrow' => 'BelongsTo + HasMany',
                'title' => 'Products → category + reviews',
                'summary' => 'Each product resolves its parent category and child reviews through relation metadata.',
                'columns' => ['Product', 'Category', 'Reviews'],
                'rows' => $productRows,
                'emptyMessage' => 'No related products available.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }

    /**
     * @return array{product_count: int, sample_products: list<string>}
     */
    private function fetchCategorySnapshot(DemoCategoryResource $category): array
    {
        $categorySlug = $category->slug;

        $count = (int) ($this->db->execute(
            'SELECT COUNT(*) AS aggregate_count
             FROM demo_products products
             INNER JOIN demo_categories categories ON categories.id = products.category_id
             WHERE categories.slug = ?',
            [$categorySlug],
        )->fetchOne()['aggregate_count'] ?? 0);

        $rows = $this->db->execute(
            'SELECT products.name
             FROM demo_products products
             INNER JOIN demo_categories categories ON categories.id = products.category_id
             WHERE categories.slug = ?
             ORDER BY products.name ASC
             LIMIT 2',
            [$categorySlug],
        )->fetchAll();

        return [
            'product_count' => $count,
            'sample_products' => array_values(array_map(
                static fn (array $row): string => (string) ($row['name'] ?? 'Unknown product'),
                $rows,
            )),
        ];
    }

    private function fetchReviewCount(string $productName): int
    {
        return (int) ($this->db->execute(
            'SELECT COUNT(*) AS aggregate_count
             FROM demo_reviews reviews
             INNER JOIN demo_products products ON products.id = reviews.product_id
             WHERE products.name = ?',
            [$productName],
        )->fetchOne()['aggregate_count'] ?? 0);
    }

    /**
     * @param list<DemoCategoryResource> $categories
     */
    private function resolveCategoryName(DemoProductResource $product, array $categories): string
    {
        foreach ($categories as $category) {
            if ($category->name !== '' && $this->isProductInCategory($product->name, $category->slug)) {
                return $category->name;
            }
        }

        $row = $this->db->execute(
            'SELECT categories.name
             FROM demo_products products
             INNER JOIN demo_categories categories ON categories.id = products.category_id
             WHERE products.name = ?
             LIMIT 1',
            [$product->name],
        )->fetchOne();

        return (string) ($row['name'] ?? 'Unassigned');
    }

    private function isProductInCategory(string $productName, string $categorySlug): bool
    {
        return (bool) ($this->db->execute(
            'SELECT 1
             FROM demo_products products
             INNER JOIN demo_categories categories ON categories.id = products.category_id
             WHERE products.name = ? AND categories.slug = ?
             LIMIT 1',
            [$productName, $categorySlug],
        )->fetchOne() !== null);
    }
}
