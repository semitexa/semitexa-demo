<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service\Feature;

use Semitexa\Demo\Application\Service\DemoFeaturePresentation;

/**
 * Immutable description of a single demo feature page.
 *
 * Composes the authoring-side {@see DemoFeaturePresentation} (title, summary,
 * highlights, body, L2/L3 previews) with the navigation-side metadata that
 * handlers contribute locally (section, slug, labels, related links).
 *
 * Consumed by {@see \Semitexa\Demo\Application\Resource\Response\DemoFeatureResource::applyFeature()}
 * as the single source of truth for a feature page's presentation state.
 */
final readonly class FeatureDescriptor
{
    /**
     * @param list<array{href: string, label: string}> $relatedPayloads
     */
    public function __construct(
        public string $section,
        public ?string $sectionLabel,
        public string $slug,
        public string $entryLine,
        public string $learnMoreLabel,
        public string $deepDiveLabel,
        public array $relatedPayloads,
        public DemoFeaturePresentation $presentation,
        public string $pageTitleSuffix = ' — Semitexa Framework',
    ) {}

    public function pageTitle(): string
    {
        return $this->presentation->title . $this->pageTitleSuffix;
    }

    /**
     * SEO keywords for this feature. Defaults to the curated highlights.
     *
     * @return list<string>
     */
    public function seoKeywords(): array
    {
        return $this->presentation->highlights;
    }
}
