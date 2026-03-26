<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo',
    slot: 'demo_sidebar',
    template: '@project-layouts-semitexa-demo/partials/sidebar.html.twig',
    priority: 90,
)]
final class DemoSidebarSlot extends HtmlSlotResponse
{
    public function withFeatureTree(array $featureTree): static
    {
        return $this->with('featureTree', $featureTree);
    }

    public function withCurrentSection(?string $section): static
    {
        return $this->with('currentSection', $section);
    }

    public function withCurrentSlug(?string $slug): static
    {
        return $this->with('currentSlug', $slug);
    }
}
