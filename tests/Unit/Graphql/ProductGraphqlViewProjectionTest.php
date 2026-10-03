<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Graphql;

use PHPUnit\Framework\TestCase;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Demo\Domain\Repository\DemoCategoryRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoReviewRepositoryInterface;

/**
 * The GraphQL view of a products page. `{ products { items { name } } }` used to
 * run one review query and one category read per product on the page, and a
 * review without a rating or text came out as 0 and "" where REST says null.
 */
final class ProductGraphqlViewProjectionTest extends TestCase
{
    public function test_a_page_reads_its_reviews_and_categories_once(): void
    {
        $products = $this->createMock(DemoProductRepositoryInterface::class);
        $products->method('findFiltered')->willReturn([
            $this->product('p1', 'Desk Lamp', 'c1'),
            $this->product('p2', 'Wall Clock', null),
            $this->product('p3', 'Floor Rug', 'c1'),
        ]);

        $reviews = $this->createMock(DemoReviewRepositoryInterface::class);
        $reviews->expects(self::never())->method('findByProduct');
        $reviews->expects(self::once())->method('findByProducts')
            ->with(['p1', 'p2', 'p3'])
            ->willReturn(['p1' => [$this->review('r1', 'p1', 5, 'Bright.')]]);

        $categories = $this->createMock(DemoCategoryRepositoryInterface::class);
        $categories->expects(self::once())->method('findAllOrdered')->willReturn([$this->category('c1', 'lighting', 'Lighting')]);

        $view = $this->presenter($products, $reviews, $categories)->buildCollectionView(null, null, 1, 24);

        self::assertSame(['Desk Lamp', 'Wall Clock', 'Floor Rug'], array_map(static fn ($item): string => $item->name, $view->items));
        self::assertSame('lighting', $view->items[0]->category?->slug);
        self::assertNull($view->items[1]->category);
        self::assertSame('r1', $view->items[0]->reviews[0]->id);
        self::assertSame(5, $view->items[0]->reviews[0]->rating);
        self::assertSame([], $view->items[1]->reviews);
        self::assertSame(3, $view->total);
    }

    public function test_a_review_without_rating_or_text_stays_null(): void
    {
        $reviews = $this->createMock(DemoReviewRepositoryInterface::class);
        $reviews->method('findByProduct')->willReturn([$this->review('r9', 'p1', null, null)]);
        $categories = $this->createMock(DemoCategoryRepositoryInterface::class);
        $categories->method('findAllOrdered')->willReturn([]);

        $view = $this->presenter($this->createMock(DemoProductRepositoryInterface::class), $reviews, $categories)
            ->buildProductView($this->product('p1', 'Desk Lamp', null));

        self::assertSame('r9', $view->reviews[0]->id);
        self::assertNull($view->reviews[0]->rating);
        self::assertNull($view->reviews[0]->headline);
    }

    private function presenter(
        DemoProductRepositoryInterface $products,
        DemoReviewRepositoryInterface $reviews,
        DemoCategoryRepositoryInterface $categories,
    ): DemoApiPresenter {
        $presenter = new DemoApiPresenter();
        foreach (['products' => $products, 'reviews' => $reviews, 'categories' => $categories] as $property => $value) {
            (new \ReflectionProperty(DemoApiPresenter::class, $property))->setValue($presenter, $value);
        }

        return $presenter;
    }

    private function product(string $id, string $name, ?string $categoryId): DemoProduct
    {
        $product = new DemoProduct();
        $product->setId($id);
        $product->setName($name);
        $product->setPrice('10.00');
        $product->setCategoryId($categoryId);

        return $product;
    }

    private function review(string $id, string $productId, ?int $rating, ?string $body): DemoReview
    {
        $review = new DemoReview();
        $review->setId($id);
        $review->setProductId($productId);
        $review->setUserId('u1');
        $review->setRating($rating);
        $review->setBody($body);

        return $review;
    }

    private function category(string $id, string $slug, string $name): DemoCategory
    {
        $category = new DemoCategory();
        $category->setId($id);
        $category->setSlug($slug);
        $category->setName($name);

        return $category;
    }
}
