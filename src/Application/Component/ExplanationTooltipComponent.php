<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attribute\AsComponent;

/**
 * Hover tooltip for framework keywords.
 *
 * Triggered by data-explain="..." attributes in rendered content.
 * Keyword definitions are passed from the current feature payload. Shows one-sentence
 * definitions only — never paragraphs. Tooltips are the only
 * explanation mechanism at L1.
 */
#[AsComponent(
    name: 'demo-explanation-tooltip',
    template: '@project-layouts-semitexa-demo/components/explanation-tooltip.html.twig',
    cacheable: true,
)]
final class ExplanationTooltipComponent
{
    public function __construct(
        public readonly string $term,
        public readonly string $definition,
    ) {}
}
