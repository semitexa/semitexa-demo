<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

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
