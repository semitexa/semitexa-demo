<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attributes\AsComponent;

/**
 * Trigger button for layer transitions (L1→L2 or L2→L3).
 *
 * Two variants:
 * - learnMore: prominent colored button, large tap target, subtle pulse on first view
 * - deepDive: muted text link with arrow, positioned at bottom of L2 content
 *
 * Emits a disclosure:expand custom event targeting the specified section ID.
 */
#[AsComponent(
    name: 'demo-disclosure-prompt',
    template: '@project-layouts-semitexa-demo/components/disclosure-prompt.html.twig',
    cacheable: false,
)]
final class DisclosurePromptComponent
{
    public function __construct(
        public readonly string $label,
        public readonly string $variant,
        public readonly string $target,
    ) {}
}
