<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_blocks',
    slot: 'deferred_search_filter',
    template: '@project-layouts-semitexa-demo/deferred/search-filter.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/search-filter.skeleton.html.twig',
    clientModules: ['@project-static-semitexa-demo/deferred/search-filter.js'],
)]
final class DeferredSearchFilterSlot extends HtmlSlotResponse
{
    public function withCategories(array $categories): static
    {
        return $this->with('categories', $categories);
    }

    public function withPriceRange(float $min, float $max): static
    {
        return $this->with('priceMin', $min)->with('priceMax', $max);
    }
}
