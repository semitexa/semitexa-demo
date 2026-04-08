<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoCategoryResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoProductResource;
use Semitexa\Demo\Application\Db\MySQL\Model\DemoReviewResource;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoCategoryRepository;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoProductRepository;
use Semitexa\Demo\Application\Db\MySQL\Repository\DemoReviewRepository;
use Semitexa\Demo\Application\Payload\Request\Data\RelationsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

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

    public function handle(RelationsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $categories = array_slice($this->categoryRepository->findAllOrdered(), 0, 2);
        $products = $this->productRepository->findPage(3);
        $focusProduct = $products[0] ?? null;
        $focusCategory = $this->resolveProductCategory($focusProduct);
        $focusReviews = $focusProduct !== null ? $this->reviewRepository->findByProduct($focusProduct->id) : [];
        $firstReview = $focusReviews[0] ?? null;

        $explanation = $this->explanationProvider->getExplanation('data', 'relations') ?? [];

        $sourceCode = [
            'Product Resource' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/ProductResource.example.php'),
            'Category Resource' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/CategoryResource.example.php'),
            'Review Resource' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/ReviewResource.example.php'),
            'Handler Read Flow' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/RelationsHandler.example.php'),
        ];

        return $resource
            ->pageTitle('Relations — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'data',
                'currentSlug' => 'relations',
                'infoWhat' => $explanation['what'] ?? 'Relations live on the resource fields themselves, so handlers read a typed object graph instead of rebuilding joins by hand.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('data')
            ->withSlug('relations')
            ->withTitle('Relations')
            ->withSummary('Declare parent and child links on the resource itself, then read typed relations from the handler.')
            ->withEntryLine('Declare parent and child links on the resource itself, then read typed relations from the handler.')
            ->withHighlights(['#[HasMany]', '#[BelongsTo]', 'foreignKey', 'typed relations', 'batch loading'])
            ->withLearnMoreLabel('See the relation attributes →')
            ->withDeepDiveLabel('How handler reads relations →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Relation Map',
                'title' => 'How links are declared',
                'summary' => 'Relations are declared directly on resource properties. Each field names the target resource and the foreign key that connects the records.',
                'stats' => [
                    ['value' => '4', 'label' => 'Declared links'],
                    ['value' => '2', 'label' => 'Bidirectional pairs'],
                    ['value' => (string) count($focusReviews), 'label' => 'Reviews on sample product'],
                ],
                'columns' => ['Resource field', 'Attribute', 'Target', 'What it gives you'],
                'rows' => [
                    [
                        ['text' => 'DemoCategoryResource::$products', 'code' => true],
                        ['text' => "#[HasMany(target: DemoProductResource::class, foreignKey: 'category_id')]", 'code' => true],
                        ['text' => 'DemoProductResource[]', 'code' => true],
                        ['text' => $this->describeCategoryProducts($categories[0] ?? null)],
                    ],
                    [
                        ['text' => 'DemoProductResource::$category', 'code' => true],
                        ['text' => "#[BelongsTo(target: DemoCategoryResource::class, foreignKey: 'category_id')]", 'code' => true],
                        ['text' => 'DemoCategoryResource', 'code' => true],
                        ['text' => $this->describeProductCategory($focusProduct, $focusCategory)],
                    ],
                    [
                        ['text' => 'DemoProductResource::$reviews', 'code' => true],
                        ['text' => "#[HasMany(target: DemoReviewResource::class, foreignKey: 'product_id')]", 'code' => true],
                        ['text' => 'DemoReviewResource[]', 'code' => true],
                        ['text' => $this->describeProductReviews($focusProduct, $focusReviews)],
                    ],
                    [
                        ['text' => 'DemoReviewResource::$product', 'code' => true],
                        ['text' => "#[BelongsTo(target: DemoProductResource::class, foreignKey: 'product_id')]", 'code' => true],
                        ['text' => 'DemoProductResource', 'code' => true],
                        ['text' => $this->describeReviewProduct($firstReview, $focusProduct)],
                    ],
                ],
                'emptyMessage' => 'No relation metadata available.',
            ])
            ->withL3ContentTemplate('@project-layouts-semitexa-demo/components/previews/data-table.html.twig', [
                'eyebrow' => 'Read Flow',
                'title' => 'How a handler consumes those links',
                'summary' => 'The handler asks a repository for typed resources. From there it can walk to parent and child records through the declared relation fields.',
                'codeSnippet' => <<<'PHP'
$products = $this->productRepository->findPage(3);
$product = $products[0] ?? null;

$categoryName = $product?->category?->name;
$reviewCount = count($product?->reviews ?? []);

$firstReviewProduct = $product?->reviews[0]?->product?->name ?? null;
PHP,
                'columns' => ['Step', 'Handler code', 'What becomes available'],
                'rows' => [
                    [
                        ['text' => 'Fetch typed rows'],
                        ['text' => '$this->productRepository->findPage(3)', 'code' => true],
                        ['text' => sprintf('%d DemoProductResource items for the screen', count($products))],
                    ],
                    [
                        ['text' => 'Walk to parent'],
                        ['text' => '$product->category?->name', 'code' => true],
                        ['text' => $this->describeProductCategory($focusProduct, $focusCategory)],
                    ],
                    [
                        ['text' => 'Walk to children'],
                        ['text' => 'count($product->reviews)', 'code' => true],
                        ['text' => $this->describeProductReviews($focusProduct, $focusReviews)],
                    ],
                    [
                        ['text' => 'Walk back from child'],
                        ['text' => '$review->product?->name', 'code' => true],
                        ['text' => $this->describeReviewProduct($firstReview, $focusProduct)],
                    ],
                ],
                'emptyMessage' => 'No relation reads available.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }

    private function resolveProductCategory(?DemoProductResource $product): ?DemoCategoryResource
    {
        if ($product === null || $product->category_id === null || $product->category_id === '') {
            return null;
        }

        return $this->categoryRepository->findById($product->category_id);
    }

    private function describeCategoryProducts(?DemoCategoryResource $category): string
    {
        if ($category === null) {
            return 'One category field opens a child collection.';
        }

        $products = $this->productRepository->findByCategory($category->id);
        $sample = $products[0] ?? null;

        return $sample !== null
            ? sprintf('%s currently groups %d products such as %s.', $category->name, count($products), $sample->name)
            : sprintf('%s can expose its linked products without manual joins in the handler.', $category->name);
    }

    private function describeProductCategory(?DemoProductResource $product, ?DemoCategoryResource $category): string
    {
        if ($product === null) {
            return 'A product can point to exactly one parent category.';
        }

        return $category !== null
            ? sprintf('%s belongs to %s through category_id.', $product->name, $category->name)
            : sprintf('%s keeps the parent link on $category.', $product->name);
    }

    /**
     * @param list<DemoReviewResource> $reviews
     */
    private function describeProductReviews(?DemoProductResource $product, array $reviews): string
    {
        if ($product === null) {
            return 'A product can expose many child reviews.';
        }

        return sprintf('%s currently exposes %d linked reviews.', $product->name, count($reviews));
    }

    private function describeReviewProduct(?DemoReviewResource $review, ?DemoProductResource $product): string
    {
        if ($review === null || $product === null) {
            return 'A review can walk back to the product it belongs to.';
        }

        $rating = $review->rating !== null ? sprintf('%d-star', $review->rating) : 'One';

        return sprintf('%s review points back to %s.', $rating, $product->name);
    }
}
