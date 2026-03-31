<?php

use App\Domain\Catalog\CatalogInterface;
use App\Domain\Product;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\AsResource;
use Semitexa\Core\Exception\NotFoundException;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsPayload(path: '/products/{slug}', methods: ['GET'], responseWith: ProductPageResource::class)]
final class ProductPagePayload
{
    protected string $slug = '';

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): void { $this->slug = $slug; }
}

#[AsPayloadHandler(payload: ProductPagePayload::class, resource: ProductPageResource::class)]
final class ProductPageHandler
{
    public function __construct(
        private readonly CatalogInterface $catalog,
    ) {}

    public function handle(ProductPagePayload $payload, ProductPageResource $resource): ProductPageResource
    {
        $product = $this->catalog->getBySlug($payload->getSlug());
        if ($product === null) {
            throw new NotFoundException('Product', $payload->getSlug());
        }

        return $resource
            ->withProduct($product)
            ->withHeroActions(['buy', 'bookmark'])
            ->withInventoryState($product->inventoryState());
    }
}

#[AsResource(handle: 'product_page', template: '@shop/pages/product.html.twig')]
final class ProductPageResource extends HtmlResponse
{
    public function withProduct(Product $product): self
    {
        return $this->with('product', $product);
    }

    public function withInventoryState(string $state): self
    {
        return $this->with('inventoryState', $state);
    }
}
