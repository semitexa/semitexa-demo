<?php

#[AsSlotResource(handle: 'product_page', slot: 'sidebar')]
final class ProductSidebarSlot extends HtmlSlotResponse
{
    public function withRecommendations(array $products): self
    {
        return $this->with('recommendations', $products);
    }
}

#[AsSlotHandler(slot: ProductSidebarSlot::class)]
final class ProductSidebarSlotHandler
{
    public function handle(ProductSidebarSlot $slot): ProductSidebarSlot
    {
        return $slot->withRecommendations(
            $this->recommendations->topForCurrentUser(),
        );
    }
}

/*
layout.twig

<aside class="page-sidebar">
  {{ layout_slot('sidebar') }}
</aside>
*/
