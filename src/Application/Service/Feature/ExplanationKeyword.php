<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service\Feature;

/**
 * One keyword a feature page explains: the term as it appears in the code, and
 * a single sentence saying what it is.
 *
 * Both are required, and that is a fact about the data rather than a wish.
 * MEASURED 2026-09-17, after the definition-less keywords were removed: all 67
 * keyword blocks in this package are literal arrays and not one contains a bare
 * term. A keyword with no definition renders as an empty `<dt>/<dd>` pair in
 * `partials/feature-info.html.twig` and is skipped entirely by the rich-highlight
 * loop in `pages/feature.html.twig` — so it can only ever be invisible or broken,
 * and the type now refuses it.
 *
 * A term that has no definition worth writing is a highlight, not a keyword:
 * pass it to `FeatureSpec::$fallbackHighlights`, which is what the five Platform
 * handlers were already doing with the same strings.
 */
final readonly class ExplanationKeyword
{
    public function __construct(
        public string $term,
        public string $definition,
    ) {}

    /**
     * @return array{term: string, definition: string}
     */
    public function toArray(): array
    {
        return ['term' => $this->term, 'definition' => $this->definition];
    }
}
