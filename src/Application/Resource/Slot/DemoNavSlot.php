<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlSlotResponse;

/**
 * NOT RENDERED BY ANY PAGE. The partial this slot names is pulled into the
 * demo layout by a direct `{% include %}`, so the slot REGISTRATION under
 * `demo_nav` is never reached: every `layout_slot()` call in the tree names a
 * different slot (`nav`, `sidebar`, `header`…), and nothing bridges the two
 * names. Left in place rather than deleted because the class is also the
 * worked example the docs point at — see tk-slots-nobody-renders.
 */
#[AsSlotResource(
    handle: 'demo',
    slot: 'demo_nav',
    template: '@project-layouts-semitexa-demo/partials/nav.html.twig',
    priority: 100,
)]
final class DemoNavSlot extends HtmlSlotResponse
{
    public function withCurrentSection(?string $section): static
    {
        return $this->with('currentSection', $section);
    }

    public function withSections(array $sections): static
    {
        return $this->with('sections', $sections);
    }
}
