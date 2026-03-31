<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Component;

use Semitexa\Ssr\Attributes\AsComponent;

/**
 * Syntax-highlighted code display with tab switching.
 *
 * Supports PHP, Twig, JSON with tabs (Payload / Handler / Resource / Template).
 * Features: line highlighting, copy button, collapsible sections.
 * Placed inside L2 zone only — never visible at L1 on page load.
 */
#[AsComponent(
    name: 'demo-code-block',
    template: '@project-layouts-semitexa-demo/components/code-block.html.twig',
    cacheable: false,
    script: 'semitexa-demo:js:code-tabs',
)]
final class CodeBlockComponent
{
    /**
     * @param array<string, string> $tabs Tab label => source code content
     */
    public function __construct(
        public readonly array $tabs,
        public readonly string $featureSlug = '',
    ) {}
}
