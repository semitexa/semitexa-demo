<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Graphql;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;
use Semitexa\Core\Resource\Metadata\ResourceMetadataExtractor;
use Semitexa\Core\Resource\Metadata\ResourceMetadataRegistry;
use Semitexa\Demo\Application\Payload\Request\Api\ProductListPayload;
use Semitexa\Demo\Application\Resource\Response\DemoApiResponse;
use Semitexa\Demo\Application\Resource\Response\Graphql\ProductCategoryGraphqlView;
use Semitexa\Demo\Application\Resource\Response\Graphql\ProductGraphqlView;
use Semitexa\Demo\Application\Resource\Response\Graphql\ProductListGraphqlView;
use Semitexa\Demo\Application\Resource\Response\Graphql\ProductReviewGraphqlView;
use Semitexa\Graphql\Application\Service\Runtime\RenderContextResourceSerializer;
use Semitexa\Graphql\Application\Service\Schema\OutputTypeRegistry;
use Semitexa\Graphql\Application\Service\Schema\PayloadArgumentBuilder;
use Semitexa\Graphql\Application\Service\Schema\ScalarTypeMapper;

/**
 * `{ products { total page limit } }` on framework.semitexa.com/graphql answered
 * {"data":{"products":null}} while GET /demo/api/v1/products was 200 (measured
 * 2026-10-01). Three separate gaps sat behind it; each is pinned here.
 */
final class ProductGraphqlFieldsTest extends TestCase
{
    /** The REST body never reached GraphQL: it was written with setContent(), not the render context. */
    public function test_the_shared_response_hands_graphql_its_typed_view_and_keeps_the_rest_body(): void
    {
        $view = new ProductListGraphqlView(items: [], total: 12, page: 1, limit: 8);
        $resource = (new DemoApiResponse())
            ->withJsonPayload(['data' => [], 'meta' => ['total' => 12]])
            ->withGraphqlProjection(static fn (): object => $view);

        self::assertSame($view, (new RenderContextResourceSerializer())->serialize($resource));
        // A non-empty render context would make the HTTP renderer replace the body.
        self::assertSame([], $resource->getRenderContext());
        self::assertStringContainsString('"total": 12', $resource->getContent());
    }

    /** A plain output DTO exposes only scalars: `items`, `category` and `reviews` were not in the schema. */
    public function test_the_schema_reaches_the_items_their_category_and_reviews(): void
    {
        $list = $this->outputTypes()->forResource(
            (new ResourceMetadataExtractor())->extract(ProductListGraphqlView::class),
        );
        self::assertInstanceOf(ObjectType::class, $list);
        self::assertEqualsCanonicalizing(['items', 'total', 'page', 'limit'], array_keys($list->getFields()));

        $items = $list->getField('items')->getType();
        $items = $items instanceof NonNull ? $items->getWrappedType() : $items;
        self::assertInstanceOf(ListOfType::class, $items);
        $product = $items->getWrappedType();
        $product = $product instanceof NonNull ? $product->getWrappedType() : $product;
        self::assertInstanceOf(ObjectType::class, $product);
        self::assertSame('ProductGraphqlView', $product->name);
        self::assertArrayHasKey('category', $product->getFields());
        self::assertArrayHasKey('reviews', $product->getFields());
    }

    /** An `int|string|null` setter is skipped by the argument builder: `products(limit: 2)` was rejected. */
    public function test_paging_is_an_argument_of_the_products_field(): void
    {
        $builder = new PayloadArgumentBuilder();
        $builder->setScalarsForTest(new ScalarTypeMapper());

        $args = $builder->buildFor(ProductListPayload::class);

        self::assertArrayHasKey('page', $args);
        self::assertArrayHasKey('limit', $args);
    }

    private function outputTypes(): OutputTypeRegistry
    {
        $extractor = new ResourceMetadataExtractor();
        $resources = new ResourceMetadataRegistry();
        foreach ([
            ProductListGraphqlView::class,
            ProductGraphqlView::class,
            ProductCategoryGraphqlView::class,
            ProductReviewGraphqlView::class,
        ] as $class) {
            $resources->register($extractor->extract($class));
        }

        $registry = new OutputTypeRegistry();
        $registry->setScalarsForTest(new ScalarTypeMapper());
        $registry->setResourcesForTest($resources);

        return $registry;
    }
}
