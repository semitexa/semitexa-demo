<?php

declare(strict_types=1);

use App\Domain\Catalog\CatalogInterface;
use App\Domain\Product;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\NotFoundException;
use Semitexa\Core\Exception\ValidationException;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsPayload(
    path: '/products/{slug}',
    methods: ['GET'],
    responseWith: ProductPageResource::class,
    requirements: ['slug' => '[a-z0-9-]+'],
)]
final class ProductPagePayload
{
    protected string $slug = '';

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $slug = strtolower(trim($slug));

        if ($slug === '') {
            throw new ValidationException(['slug' => ['Product slug is required.']]);
        }

        if (preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
            throw new ValidationException(['slug' => ['Product slug may only contain lowercase letters, numbers, and dashes.']]);
        }

        $this->slug = $slug;
    }
}

#[AsPayloadHandler(payload: ProductPagePayload::class, resource: ProductPageResource::class)]
final class ProductPageHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    private CatalogInterface $catalog;

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

    public function withHeroActions(array $actions): self
    {
        return $this->with('heroActions', $actions);
    }
}
