<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Semitexa\Core\Request;
use Semitexa\Core\Resource\AcceptHeaderResolver;
use Semitexa\Demo\Application\Service\DemoApiPresenter;
use Semitexa\Demo\Domain\Model\DemoCategory;
use Semitexa\Demo\Domain\Model\DemoProduct;
use Semitexa\Demo\Domain\Model\DemoReview;
use Semitexa\Demo\Domain\Repository\DemoCategoryRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoProductRepositoryInterface;
use Semitexa\Demo\Domain\Repository\DemoReviewRepositoryInterface;

/**
 * The REST collection used to run two review queries and one category read per
 * product on the page (/demo/api/v1/products, active-version, sunset-version),
 * and a single product read its reviews up to three times.
 */
final class DemoApiPresenterReadsTest extends TestCase
{
    public function test_a_collection_reads_its_reviews_and_categories_once(): void
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
            ->willReturn(['p1' => [
                $this->review('r2', 'p1', 4, 'Warm.'),
                $this->review('r1', 'p1', null, null),
            ]]);

        $categories = $this->createMock(DemoCategoryRepositoryInterface::class);
        $categories->expects(self::once())->method('findAllOrdered')->willReturn([$this->category('c1', 'lighting', 'Lighting')]);

        $body = $this->presenter($products, $reviews, $categories)->buildCollection(
            request: $this->request('/demo/api/v1/products'),
            profile: 'full',
            limit: 24,
        );

        $items = $body['data'];
        self::assertSame(['Desk Lamp', 'Wall Clock', 'Floor Rug'], array_column($items, 'name'));
        self::assertSame(['slug' => 'lighting', 'name' => 'Lighting'], $items[0]['category']);
        self::assertNull($items[1]['category']);
        self::assertSame(2, $items[0]['reviewCount']);
        self::assertSame(2.0, $items[0]['rating']);
        self::assertSame([
            ['user' => 'u1', 'rating' => 4, 'body' => 'Warm.'],
            ['user' => 'u1', 'rating' => null, 'body' => null],
        ], $items[0]['reviews']);
        self::assertSame(0, $items[1]['reviewCount']);
        self::assertSame(0.0, $items[1]['rating']);
        self::assertSame([], $items[1]['reviews']);
    }

    public function test_a_sparse_detail_reads_no_relation(): void
    {
        $reviews = $this->createMock(DemoReviewRepositoryInterface::class);
        $reviews->expects(self::never())->method('findByProduct');
        $categories = $this->createMock(DemoCategoryRepositoryInterface::class);
        $categories->expects(self::never())->method('findAllOrdered');

        $body = $this->presenter($this->createMock(DemoProductRepositoryInterface::class), $reviews, $categories)
            ->buildDetailFor($this->product('p1', 'Desk Lamp', 'c1'), $this->request('/demo/api/v1/products/desk-lamp'), fields: 'slug,name,price');

        self::assertSame(['slug', 'name', 'price', 'links'], array_keys($body['data']));
    }

    public function test_a_full_detail_reads_its_reviews_once(): void
    {
        $reviews = $this->createMock(DemoReviewRepositoryInterface::class);
        $reviews->expects(self::once())->method('findByProduct')->with('p1')->willReturn([]);
        $categories = $this->createMock(DemoCategoryRepositoryInterface::class);
        $categories->expects(self::once())->method('findAllOrdered')->willReturn([]);

        $body = $this->presenter($this->createMock(DemoProductRepositoryInterface::class), $reviews, $categories)
            ->buildDetailFor($this->product('p1', 'Desk Lamp', 'c1'), $this->request('/demo/api/v1/products/desk-lamp'), expand: 'reviews', profile: 'full');

        self::assertSame(0, $body['data']['reviewCount']);
        self::assertSame(0.0, $body['data']['rating']);
        self::assertSame([], $body['data']['reviews']);
    }

    private function request(string $path): Request
    {
        return new Request('GET', $path, ['Accept' => 'application/json'], [], [], [], []);
    }

    private function presenter(
        DemoProductRepositoryInterface $products,
        DemoReviewRepositoryInterface $reviews,
        DemoCategoryRepositoryInterface $categories,
    ): DemoApiPresenter {
        $presenter = new DemoApiPresenter();
        $dependencies = [
            'products' => $products,
            'reviews' => $reviews,
            'categories' => $categories,
            'acceptResolver' => new AcceptHeaderResolver(),
        ];
        foreach ($dependencies as $property => $value) {
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
