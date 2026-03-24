<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attributes\AsComponent;

/**
 * Wraps L3 (advanced) content. Hidden by default.
 *
 * Desktop: slide-in panel from right (400px) with gray background tint.
 * Tablet/mobile: renders as below-fold expandable section.
 */
#[AsComponent(
    name: 'demo-deep-dive-drawer',
    template: '@project-layouts-semitexa-demo/components/deep-dive-drawer.html.twig',
    cacheable: false,
)]
final class DeepDiveDrawerComponent
{
    public function __construct(
        public readonly string $targetId,
    ) {}
}
