<?php

declare(strict_types=1);

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'product_showcase',
    template: '@project/product/showcase.html.twig',
)]
final class ProductShowcaseResource extends HtmlResponse
{
    public function withProductName(string $name): self
    {
        return $this->with('productName', $name);
    }

    public function withPriceLabel(string $priceLabel): self
    {
        return $this->with('priceLabel', $priceLabel);
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
