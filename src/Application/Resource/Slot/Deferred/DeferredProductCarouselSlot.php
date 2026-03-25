<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_blocks',
    slot: 'deferred_product_carousel',
    template: '@project-layouts-semitexa-demo/deferred/product-carousel.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/product-carousel.skeleton.html.twig',
    clientModules: ['deferred/product-carousel.js'],
)]
final class DeferredProductCarouselSlot extends HtmlSlotResponse
{
    public function withProducts(array $products): static
    {
        return $this->with('products', $products);
    }
}
