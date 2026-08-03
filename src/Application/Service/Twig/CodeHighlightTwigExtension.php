<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service\Twig;

use Semitexa\Ssr\Application\Service\Code\CodeHighlighter;
use Semitexa\Ssr\Application\Service\Extension\TwigExtensionRegistry;
use Semitexa\Ssr\Attribute\AsTwigExtension;
use Twig\Markup;

/**
 * Exposes the shared code highlighter to demo templates.
 *
 * This class used to carry its own copy of {@see CodeHighlighter}: twelve of its
 * thirteen methods — 183 statements, the whole tokenizer — were byte-identical
 * to the one in semitexa-showcase-kit. Neither had drifted, which is the only
 * reason it went unnoticed; the next fix would have landed in one and quietly
 * not the other. ep-duplication-sweep moved the algorithm to semitexa-ssr, which
 * both packages already depend on, leaving each with its own thin wrapper.
 *
 * All three functions are registered `is_safe => html` because they exist to
 * emit markup — the highlighter escapes the source it wraps, and falls back to
 * plain escaped output when the source will not parse.
 */
#[AsTwigExtension]
final class CodeHighlightTwigExtension
{
    public function registerFunctions(): void
    {
        TwigExtensionRegistry::registerFunction(
            'highlight_snippet',
            [$this, 'highlightSnippet'],
            ['is_safe' => ['html']],
        );
        TwigExtensionRegistry::registerFunction(
            'highlight_php',
            [$this, 'highlightPhp'],
            ['is_safe' => ['html']],
        );
        TwigExtensionRegistry::registerFunction(
            'highlight_php_lines',
            [$this, 'highlightPhpLines'],
            ['is_safe' => ['html']],
        );
    }

    /**
     * Highlight a block, auto-detecting PHP, shell or JSON.
     *
     * `$mixedDepth` is forwarded untouched — it is how the highlighter stops
     * recursing on a block that interleaves shell and PHP.
     */
    public function highlightPhp(mixed $source, int $mixedDepth = 0): Markup
    {
        return (new CodeHighlighter())->highlightPhp($source, $mixedDepth);
    }

    /**
     * Highlight a short inline fragment.
     */
    public function highlightSnippet(mixed $source): Markup
    {
        return (new CodeHighlighter())->highlightSnippet($source);
    }

    /**
     * Highlight a block as numbered lines, for templates that render a gutter.
     */
    public function highlightPhpLines(mixed $source): Markup
    {
        return (new CodeHighlighter())->highlightPhpLines($source);
    }
}
