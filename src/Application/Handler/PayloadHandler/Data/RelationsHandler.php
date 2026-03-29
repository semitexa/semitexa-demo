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
        $categorySnapshots = $this->fetchCategorySnapshots($categories);
        foreach ($categories as $category) {
            $snapshot = $categorySnapshots[$category->slug] ?? [
                'product_count' => 0,
                'sample_products' => [],
            ];

            $categoryRows[] = [
                ['text' => (string) ($category->name ?? 'Unnamed category')],
                ['text' => sprintf('%d products', $snapshot['product_count'])],
                ['text' => $snapshot['sample_products'] !== [] ? implode(', ', $snapshot['sample_products']) : 'No linked products yet'],
            ];
        }

        $productMeta = $this->fetchProductMeta($products);
        $productRows = [];
        foreach ($products as $product) {
            $meta = $productMeta[$product->id] ?? ['review_count' => 0, 'category_name' => 'Unassigned'];

            $productRows[] = [
                ['text' => (string) ($product->name ?? 'Unnamed product')],
                ['text' => (string) $meta['category_name']],
                ['text' => sprintf('%d reviews', $meta['review_count'])],
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
            ->withHighlights(['#[HasMany]', '#[BelongsTo]', 'RelationWritePolicy', 'AggregateWriteEngine', 'eager loading'])
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
     * @param list<DemoCategoryResource> $categories
     *
     * @return array<string, array{product_count: int, sample_products: list<string>}>
     */
    private function fetchCategorySnapshots(array $categories): array
    {
        if ($categories === []) {
            return [];
        }

        $categoryIds = array_values(array_map(
            static fn (DemoCategoryResource $category): string => $category->id,
            $categories,
        ));
        $placeholders = implode(', ', array_fill(0, count($categoryIds), '?'));
        $snapshots = [];

        foreach ($categories as $category) {
            $snapshots[$category->slug] = [
                'product_count' => 0,
                'sample_products' => [],
            ];
        }

        $countRows = $this->db->execute(
            "SELECT categories.slug, COUNT(products.id) AS product_count
             FROM demo_categories categories
             LEFT JOIN demo_products products ON products.category_id = categories.id
             WHERE categories.id IN ({$placeholders})
             GROUP BY categories.slug",
            $categoryIds,
        )->fetchAll();

        foreach ($countRows as $row) {
            $slug = (string) ($row['slug'] ?? '');
            if ($slug === '' || !isset($snapshots[$slug])) {
                continue;
            }

            $snapshots[$slug]['product_count'] = (int) ($row['product_count'] ?? 0);
        }

        $sampleRows = $this->db->execute(
            "SELECT categories.slug, products.name
             FROM demo_categories categories
             LEFT JOIN demo_products products ON products.category_id = categories.id
             WHERE categories.id IN ({$placeholders})
             ORDER BY categories.slug ASC, products.name ASC",
            $categoryIds,
        )->fetchAll();

        foreach ($sampleRows as $row) {
            $slug = (string) ($row['slug'] ?? '');
            $name = (string) ($row['name'] ?? '');
            if ($slug === '' || $name === '' || !isset($snapshots[$slug])) {
                continue;
            }

            if (count($snapshots[$slug]['sample_products']) < 2) {
                $snapshots[$slug]['sample_products'][] = $name;
            }
        }

        return $snapshots;
    }

    /**
     * @param list<DemoProductResource> $products
     *
     * @return array<string, array{review_count: int, category_name: string}>
     */
    private function fetchProductMeta(array $products): array
    {
        if ($products === []) {
            return [];
        }

        $productIds = array_values(array_map(
            static fn (DemoProductResource $product): string => $product->id,
            $products,
        ));
        $placeholders = implode(', ', array_fill(0, count($productIds), '?'));
        $meta = [];

        foreach ($products as $product) {
            $meta[$product->id] = [
                'review_count' => 0,
                'category_name' => 'Unassigned',
            ];
        }

        $rows = $this->db->execute(
            "SELECT products.id, categories.name AS category_name, COUNT(reviews.id) AS review_count
             FROM demo_products products
             LEFT JOIN demo_categories categories ON categories.id = products.category_id
             LEFT JOIN demo_reviews reviews ON reviews.product_id = products.id
             WHERE products.id IN ({$placeholders})
             GROUP BY products.id, categories.name",
            $productIds,
        )->fetchAll();

        foreach ($rows as $row) {
            $productId = (string) ($row['id'] ?? '');
            if ($productId === '' || !isset($meta[$productId])) {
                continue;
            }

            $meta[$productId] = [
                'review_count' => (int) ($row['review_count'] ?? 0),
                'category_name' => (string) ($row['category_name'] ?? 'Unassigned'),
            ];
        }

        return $meta;
    }
}
