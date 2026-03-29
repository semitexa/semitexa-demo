<?php

declare(strict_types=1);

final class LegacyProductPageHandler
{
    public function show(Product $product, HtmlResponse $response): HtmlResponse
    {
        return $response->renderTemplate('product/show.html.twig', [
            'product' => $product,
            'price' => '$' . number_format($product->price, 2),
            'inventoryBadge' => $product->stock > 0 ? 'In stock' : 'Backorder',
            'cta' => [
                'label' => $product->stock > 0 ? 'Buy now' : 'Notify me',
                'href' => '/products/' . $product->slug,
            ],
        ]);
    }
}
