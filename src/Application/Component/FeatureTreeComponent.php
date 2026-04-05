<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

/**
 * Sidebar navigation tree for feature exploration.
 *
 * Sections ordered by complexity (Routing first, API last).
 * Current section expanded, other sections collapsed.
 * Current feature highlighted. Shows visited indicators (cookie-tracked).
 */
#[AsComponent(
    name: 'demo-feature-tree',
    template: '@project-layouts-semitexa-demo/components/feature-tree.html.twig',
    cacheable: false,
)]
final class FeatureTreeComponent
{
    public function __construct(
        public readonly array $sections,
        public readonly ?string $currentSection = null,
        public readonly ?string $currentSlug = null,
    ) {}
}
