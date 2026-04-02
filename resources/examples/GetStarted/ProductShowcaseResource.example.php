<?php

declare(strict_types=1);

use Semitexa\Core\Attributes\AsResource;
use Semitexa\Ssr\Http\Response\HtmlResponse;

#[AsResource(
    handle: 'product_showcase',
    template: '@project/catalog/pages/product-show.html.twig',
)]
final class ProductShowcaseResource extends HtmlResponse
{
    public function withSlug(string $slug): self
    {
        return $this->with('slug', $slug);
    }

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

    public function withSummary(string $summary): self
    {
        return $this->with('summary', $summary);
    }

    public function withHeroActions(array $actions): self
    {
        return $this->with('heroActions', $actions);
    }
}
