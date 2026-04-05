<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

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
