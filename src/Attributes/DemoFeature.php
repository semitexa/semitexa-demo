<?php

declare(strict_types=1);

namespace Semitexa\Demo\Attributes;

use Attribute;

/**
 * Marks a Payload DTO as a demo feature in the Semitexa showcase.
 *
 * Classes carrying this attribute are discovered by DemoFeatureRegistry
 * and used to build the feature navigation tree, section pages, and
 * progressive disclosure layout.
 *
 * Usage:
 * ```php
 * #[AsPayload(path: '/demo/routing/basic', methods: ['GET'], responseWith: DemoFeatureResource::class)]
 * #[DemoFeature(
 *     section: 'routing',
 *     title: 'Basic Route',
 *     slug: 'basic',
 *     summary: 'Define a route with one attribute',
 *     order: 1,
 *     entryLine: 'Define a route with one attribute — no XML, no YAML, no config files.',
 * )]
 * class BasicRoutePayload {}
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class DemoFeature
{
    public function __construct(
        /** Section identifier (e.g. 'routing', 'data', 'di'). */
        public readonly string $section,
        /** Human-readable feature title. */
        public readonly string $title,
        /** URL-safe slug, unique within a section. */
        public readonly string $slug,
        /** One-line summary of the feature. */
        public readonly string $summary,
        /** Sort position within the section (lower = first). */
        public readonly int $order,
        /** Framework features demonstrated by this feature (e.g. ['#[AsPayload]', 'TypedHandlerInterface']). */
        public readonly array $highlights = [],
        /** Payload class names that this feature cross-references. */
        public readonly array $relatedPayloads = [],
        /** L1 one-sentence entry text shown on the feature page before expansion. */
        public readonly string $entryLine = '',
        /** Custom label for the L1 → L2 disclosure prompt. */
        public readonly string $learnMoreLabel = 'Try it yourself →',
        /** Custom label for the L2 → L3 disclosure prompt. */
        public readonly string $deepDiveLabel = 'Under the hood →',
    ) {}
}
