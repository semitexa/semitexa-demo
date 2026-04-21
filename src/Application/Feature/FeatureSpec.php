<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Feature;

/**
 * Handler-facing description of a feature page.
 *
 * Carries only the information a handler can state by itself: the page's
 * identity (section / slug), its narrative copy (entry line, labels), its
 * related-link intent (by slug in the same section), and the fallback title /
 * summary / highlights used if the docs manifest does not resolve the page.
 *
 * Enrichment from the catalog — section label, related-link titles and
 * hrefs, the full presentation — is performed by {@see DemoFeaturePageProjector}.
 */
final readonly class FeatureSpec
{
    /**
     * @param list<string> $relatedSlugs Slugs in the same section, in display order.
     * @param list<string> $fallbackHighlights
     * @param array{what?: string|null, how?: string|null, why?: string|null, keywords?: list<array<string, mixed>>}|null $explanation
     *        Optional concept-level explanation for the page. When supplied the projector routes it
     *        into the demo shell info panel (what/how/why/keywords) and into the resource body so
     *        the feature template can render structured prose alongside the docs-backed document.
     */
    public function __construct(
        public string $section,
        public string $slug,
        public string $entryLine,
        public string $learnMoreLabel,
        public string $deepDiveLabel,
        public array $relatedSlugs,
        public string $fallbackTitle,
        public string $fallbackSummary,
        public array $fallbackHighlights = [],
        public ?array $explanation = null,
        public string $pageTitleSuffix = ' — Semitexa Framework',
        public ?string $sectionLabel = null,
    ) {}
}
