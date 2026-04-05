<?php

declare(strict_types=1);

use Semitexa\Ssr\Attribute\AsComponent;

#[AsComponent(
    name: 'product-spotlight-card',
    template: '@shop/components/product-spotlight-card.html.twig',
    script: 'shop:js:product-spotlight-card',
)]
final class ProductSpotlightCardComponent
{
    public function __construct(
        public readonly string $title,
        public readonly string $summary,
    ) {}
}
