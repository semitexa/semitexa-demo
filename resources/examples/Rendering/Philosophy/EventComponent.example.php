<?php

declare(strict_types=1);

use App\Events\DemoDisclosureExpanded;
use Semitexa\Ssr\Attributes\AsComponent;

#[AsComponent(
    name: 'disclosure-prompt',
    template: '@shop/components/disclosure-prompt.html.twig',
    event: DemoDisclosureExpanded::class,
    triggers: ['click'],
)]
final class DisclosurePromptComponent
{
    public function __construct(
        public readonly string $label,
        public readonly string $target,
    ) {}
}

/*
disclosure-prompt.html.twig

<button
  type="button"
  data-disclosure-trigger="{{ target }}"
  {{ component_event_attrs('click', {
    targetId: target,
    source: 'product-page',
  }) }}
>
  {{ label }}
</button>
*/

// The component still behaves like SSR UI, but click can now dispatch a declared backend event contract.
