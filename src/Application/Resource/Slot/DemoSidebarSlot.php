<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlSlotResponse;

/**
 * NOT RENDERED BY ANY PAGE. The partial this slot names is pulled into the
 * demo layout by a direct `{% include %}`, so the slot REGISTRATION under
 * `demo_sidebar` is never reached: every `layout_slot()` call in the tree names a
 * different slot (`nav`, `sidebar`, `header`…), and nothing bridges the two
 * names. Left in place rather than deleted because the class is also the
 * worked example the docs point at — see tk-slots-nobody-renders.
 */
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
