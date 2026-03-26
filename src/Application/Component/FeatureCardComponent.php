<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attributes\AsComponent;

/**
 * Grid card on the home page.
 *
 * Shows section title, one-sentence summary, feature count, icon.
 * No code, no architecture details — entry-level only.
 * Hover lift effect, click navigates to section landing.
 * Advanced sections show "Requires: [prerequisite]" badge.
 */
#[AsComponent(
    name: 'demo-feature-card',
    template: '@project-layouts-semitexa-demo/components/feature-card.html.twig',
)]
final class FeatureCardComponent
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $summary,
        public readonly string $icon,
        public readonly int $featureCount,
        public readonly array $prerequisites = [],
        public readonly bool $starter = false,
    ) {}
}
