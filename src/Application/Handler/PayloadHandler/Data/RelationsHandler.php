<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Data;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Data\RelationsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Demo\Domain\Repository\DemoCategoryRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoReviewRepositoryInterface;

#[AsPayloadHandler(payload: RelationsPayload::class, resource: DemoFeatureResource::class)]
final class RelationsHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoCategoryRepositoryInterface $categoryRepository;

    #[InjectAsReadonly]
    protected DemoProductRepositoryInterface $productRepository;

    #[InjectAsReadonly]
    protected DemoReviewRepositoryInterface $reviewRepository;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(RelationsPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'data',
            slug: 'relations',
            entryLine: 'Declare parent and child links on the resource itself, then read typed relations from the handler.',
            learnMoreLabel: 'See the relation attributes →',
            deepDiveLabel: 'How handler reads relations →',
            relatedSlugs: [],
            fallbackTitle: 'Relations',
            fallbackSummary: 'Declare parent and child links on the resource itself, then read typed relations from the handler.',
            fallbackHighlights: ['#[HasMany]', '#[BelongsTo]', 'foreignKey', 'typed relations', 'batch loading'],
            explanation: $this->explanationProvider->getExplanation('data', 'relations') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $categories = array_slice($this->categoryRepository->findAllOrdered(), 0, 2);
        $products = $this->productRepository->findPage(3);
        $focusProduct = $products[0] ?? null;
        $focusCategory = $this->resolveProductCategory($focusProduct);
        $focusReviews = $focusProduct !== null ? $this->reviewRepository->findByProduct($focusProduct->getId()) : [];
        $firstReview = $focusReviews[0] ?? null;

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Product Resource' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/ProductResource.example.php'),
                'Category Resource' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/CategoryResource.example.php'),
                'Review Resource' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/ReviewResource.example.php'),
                'Handler Read Flow' => $this->sourceCodeReader->readProjectRelativeSource('packages/semitexa-demo/resources/examples/Data/Relations/RelationsHandler.example.php'),
            ])
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

$category = $product !== null ? $this->categoryRepository->findById($product->getCategoryId()) : null;
$reviews = $product !== null ? $this->reviewRepository->findByProduct($product->getId()) : [];

$categoryName = $category?->getName();
$reviewCount = count($reviews);
$firstReviewProduct = $reviews[0]?->getProductId() ?? null;
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
                        ['text' => '$this->categoryRepository->findById($product->getCategoryId())?->getName()', 'code' => true],
                        ['text' => $this->describeProductCategory($focusProduct, $focusCategory)],
                    ],
                    [
                        ['text' => 'Walk to children'],
                        ['text' => 'count($this->reviewRepository->findByProduct($product->getId()))', 'code' => true],
                        ['text' => $this->describeProductReviews($focusProduct, $focusReviews)],
                    ],
                    [
                        ['text' => 'Walk back from child'],
                        ['text' => '$this->productRepository->findById($review->getProductId())?->getName()', 'code' => true],
                        ['text' => $this->describeReviewProduct($firstReview, $focusProduct)],
                    ],
                ],
                'emptyMessage' => 'No relation reads available.',
            ]);
    }

    private function resolveProductCategory(?DemoProduct $product): ?DemoCategory
    {
        if ($product === null || $product->getCategoryId() === null || $product->getCategoryId() === '') {
            return null;
        }

        return $this->categoryRepository->findById($product->getCategoryId());
    }

    private function describeCategoryProducts(?DemoCategory $category): string
    {
        if ($category === null) {
            return 'One category field opens a child collection.';
        }

        $products = $this->productRepository->findByCategory($category->getId());
        $sample = $products[0] ?? null;

        return $sample !== null
            ? sprintf('%s currently groups %d products such as %s.', $category->getName(), count($products), $sample->getName())
            : sprintf('%s can expose its linked products without manual joins in the handler.', $category->getName());
    }

    private function describeProductCategory(?DemoProduct $product, ?DemoCategory $category): string
    {
        if ($product === null) {
            return 'A product can point to exactly one parent category.';
        }

        return $category !== null
            ? sprintf('%s belongs to %s through category_id.', $product->getName(), $category->getName())
            : sprintf('%s keeps the parent link on $category.', $product->getName());
    }

    /**
     * @param list<DemoReview> $reviews
     */
    private function describeProductReviews(?DemoProduct $product, array $reviews): string
    {
        if ($product === null) {
            return 'A product can expose many child reviews.';
        }

        return sprintf('%s currently exposes %d linked reviews.', $product->getName(), count($reviews));
    }

    private function describeReviewProduct(?DemoReview $review, ?DemoProduct $product): string
    {
        if ($review === null || $product === null) {
            return 'A review can walk back to the product it belongs to.';
        }

        $rating = $review->getRating() !== null ? sprintf('%d-star', $review->getRating()) : 'One';

        return sprintf('%s review points back to %s.', $rating, $product->getName());
    }
}
