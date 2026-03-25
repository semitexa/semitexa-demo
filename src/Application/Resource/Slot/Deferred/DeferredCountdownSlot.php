<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_encapsulation',
    slot: 'deferred_countdown',
    template: '@project-layouts-semitexa-demo/deferred/countdown-timer.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/countdown-timer.skeleton.html.twig',
    clientModules: ['deferred/countdown-timer.js'],
)]
final class DeferredCountdownSlot extends HtmlSlotResponse
{
    public function withDuration(int $seconds): static
    {
        return $this->with('duration', $seconds);
    }

    public function withLabel(string $label): static
    {
        return $this->with('label', $label);
    }

    public function withInstanceId(string $id): static
    {
        return $this->with('instanceId', $id);
    }
}
