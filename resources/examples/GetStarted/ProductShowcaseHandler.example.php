<?php

declare(strict_types=1);

use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\NotFoundException;

final class ProductShowcaseHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected CatalogReadRepositoryInterface $catalog;

    public function handle(ProductShowcasePayload $payload, ProductShowcaseResource $resource): ProductShowcaseResource
    {
        $product = $this->catalog->findBySlug($payload->getSlug());

        if ($product === null) {
            throw new NotFoundException('Product', $payload->getSlug());
        }

        return $resource
            ->withSlug($product->slug)
            ->withProductName($product->name)
            ->withPriceLabel('$' . number_format($product->price, 2))
            ->withInventoryState($product->stock > 0 ? 'In stock' : 'Backorder')
            ->withSummary($product->summary)
            ->withHeroActions([
                ['label' => 'Buy now', 'href' => '/products/' . $product->slug . '/buy'],
                ['label' => 'Read specs', 'href' => '/products/' . $product->slug . '#specs'],
            ]);
    }
}
