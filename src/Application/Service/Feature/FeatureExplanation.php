<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service\Feature;

/**
 * The concept-level explanation of a feature page: what it is, how it works,
 * why it is built that way, and the terms worth defining on the way past.
 *
 * Replaces a hand-written `array{what?, how?, why?, keywords?}` shape that had
 * drifted from the data it described. MEASURED 2026-09-17: the shape allowed a
 * keyword to spell its term four ways (`term`/`title`/`label`/`name`), and of
 * the 205 keywords this package wrote as arrays — 188 in
 * DemoExplanationProvider, 17 inline in handlers — exactly zero used anything
 * but `term`. It also allowed a bare term string, and 25 of those did exist, in
 * the `DOC_KEYWORDS` constants of five Platform handlers; they were removed the
 * same day, because a keyword with no definition renders as an empty `<dt>/<dd>`
 * and those five pages carried the same strings twice over in
 * `fallbackHighlights` anyway. Four of five branches described nothing, and a
 * reader had no way to tell that from the docblock — which is what this type is
 * for.
 *
 * Empty strings are dropped on construction, so a caller cannot distinguish
 * "absent" from "blank" downstream — {@see toArray()} emits exactly the keys
 * that carry something, which is the shape the projector used to produce by
 * hand.
 */
final readonly class FeatureExplanation
{
    /** @var list<ExplanationKeyword> */
    public array $keywords;

    public ?string $what;

    public ?string $how;

    public ?string $why;

    /**
     * @param list<ExplanationKeyword> $keywords
     */
    public function __construct(
        ?string $what = null,
        ?string $how = null,
        ?string $why = null,
        array $keywords = [],
    ) {
        $this->what     = self::clean($what);
        $this->how      = self::clean($how);
        $this->why      = self::clean($why);
        $this->keywords = array_values($keywords);
    }

    /**
     * Build from a stored table row — the provider's const table, or a handler
     * that still writes its explanation inline.
     *
     * @param array{
     *     what?: string,
     *     how?: string,
     *     why?: string,
     *     keywords?: list<array{term: string, definition: string}>
     * } $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            what:     $row['what'] ?? null,
            how:      $row['how'] ?? null,
            why:      $row['why'] ?? null,
            keywords: array_map(
                static fn (array $keyword): ExplanationKeyword => new ExplanationKeyword(
                    term:       $keyword['term'],
                    definition: $keyword['definition'],
                ),
                array_values($row['keywords'] ?? []),
            ),
        );
    }

    /**
     * Nothing to show: the page falls back to its catalog summary.
     */
    public function isEmpty(): bool
    {
        return $this->what === null
            && $this->how === null
            && $this->why === null
            && $this->keywords === [];
    }

    /**
     * The presentation shape the shell context and the resource body carry.
     *
     * Only non-empty entries appear, and `keywords` only when there is at
     * least one — byte-identical to what the projector's normalizeExplanation()
     * produced before this type existed.
     *
     * @return array{
     *     what?: string,
     *     how?: string,
     *     why?: string,
     *     keywords?: list<array{term: string, definition: string}>
     * }
     */
    public function toArray(): array
    {
        $out = [];

        foreach (['what' => $this->what, 'how' => $this->how, 'why' => $this->why] as $key => $value) {
            if ($value !== null) {
                $out[$key] = $value;
            }
        }

        if ($this->keywords !== []) {
            $out['keywords'] = array_map(
                static fn (ExplanationKeyword $keyword): array => $keyword->toArray(),
                $this->keywords,
            );
        }

        return $out;
    }

    private static function clean(?string $value): ?string
    {
        return $value !== null && trim($value) !== '' ? $value : null;
    }
}
