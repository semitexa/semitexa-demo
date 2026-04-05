<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

/**
 * Wraps L2 (intermediate) content. Collapsed by default.
 *
 * Manages aria-expanded/aria-controls for accessibility.
 * Expand/collapse is driven by disclosure.js via the disclosure:expand custom event.
 */
#[AsComponent(
    name: 'demo-expandable-section',
    template: '@project-layouts-semitexa-demo/components/expandable-section.html.twig',
    cacheable: false,
)]
final class ExpandableSectionComponent
{
    public function __construct(
        public readonly string $targetId,
        public readonly bool $initiallyOpen = false,
    ) {}
}
