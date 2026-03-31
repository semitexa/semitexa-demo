<?php

declare(strict_types=1);

final class ProductShowcaseHandler
{
    public function handle(ProductPagePayload $payload, ProductShowcaseResource $resource): ProductShowcaseResource
    {
        $product = $this->catalog->findBySlug($payload->getSlug());

        return $resource
            ->withProductName($product->name)
            ->withPriceLabel('$' . number_format($product->price, 2))
            ->withInventoryState($product->stock > 0 ? 'In stock' : 'Backorder')
            ->withHeroActions([
                ['label' => 'Buy now', 'href' => '/products/' . $product->slug . '/buy'],
                ['label' => 'Read specs', 'href' => '/products/' . $product->slug . '#specs'],
            ]);
    }
}
