<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlSlotResponse;

/**
 * NOT RENDERED BY ANY PAGE. The partial this slot names is pulled into the
 * demo layout by a direct `{% include %}`, so the slot REGISTRATION under
 * `demo_feature_info` is never reached: every `layout_slot()` call in the tree names a
 * different slot (`nav`, `sidebar`, `header`…), and nothing bridges the two
 * names. Left in place rather than deleted because the class is also the
 * worked example the docs point at — see tk-slots-nobody-renders.
 */
#[AsSlotResource(
    handle: 'demo',
    slot: 'demo_feature_info',
    template: '@project-layouts-semitexa-demo/partials/feature-info.html.twig',
    priority: 80,
)]
final class DemoFeatureInfoSlot extends HtmlSlotResponse
{
    public function withWhat(string $what): static
    {
        return $this->with('what', $what);
    }

    public function withHow(string $how): static
    {
        return $this->with('how', $how);
    }

    public function withWhy(string $why): static
    {
        return $this->with('why', $why);
    }

    public function withKeywords(array $keywords): static
    {
        return $this->with('keywords', $keywords);
    }
}
