<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Twig;

use Semitexa\Ssr\Attributes\AsTwigExtension;
use Semitexa\Ssr\Extension\TwigExtensionRegistry;
use Twig\Markup;

#[AsTwigExtension]
final class CodeHighlightTwigExtension
{
    private const BUILTIN_TYPE_NAMES = [
        'array',
        'bool',
        'callable',
        'float',
        'int',
        'iterable',
        'mixed',
        'never',
        'null',
        'object',
        'parent',
        'self',
        'static',
        'string',
        'void',
    ];

    private const KEYWORD_TOKEN_NAMES = [
        'T_ABSTRACT',
        'T_ARRAY',
        'T_AS',
        'T_BREAK',
        'T_CALLABLE',
        'T_CASE',
        'T_CATCH',
        'T_CLASS',
        'T_CLONE',
        'T_CONST',
        'T_CONTINUE',
        'T_DECLARE',
        'T_DEFAULT',
        'T_DO',
        'T_ECHO',
        'T_ELSE',
        'T_ELSEIF',
        'T_EMPTY',
        'T_ENDDECLARE',
        'T_ENDFOR',
        'T_ENDFOREACH',
        'T_ENDIF',
        'T_ENDSWITCH',
        'T_ENDWHILE',
        'T_ENUM',
        'T_EVAL',
        'T_EXIT',
        'T_EXTENDS',
        'T_FINAL',
        'T_FINALLY',
        'T_FN',
        'T_FOR',
        'T_FOREACH',
        'T_FUNCTION',
        'T_GLOBAL',
        'T_GOTO',
        'T_IF',
        'T_IMPLEMENTS',
        'T_INCLUDE',
        'T_INCLUDE_ONCE',
        'T_INSTANCEOF',
        'T_INSTEADOF',
        'T_INTERFACE',
        'T_ISSET',
        'T_LIST',
        'T_MATCH',
        'T_NAMESPACE',
        'T_NEW',
        'T_PRINT',
        'T_PRIVATE',
        'T_PROTECTED',
        'T_PUBLIC',
        'T_READONLY',
        'T_REQUIRE',
        'T_REQUIRE_ONCE',
        'T_RETURN',
        'T_STATIC',
        'T_SWITCH',
        'T_THROW',
        'T_TRAIT',
        'T_TRY',
        'T_UNSET',
        'T_USE',
        'T_VAR',
        'T_WHILE',
        'T_YIELD',
        'T_YIELD_FROM',
    ];

    private const LITERAL_TOKEN_NAMES = [
        'T_TRUE',
        'T_FALSE',
        'T_NULL',
    ];

    public function registerFunctions(): void
    {
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

    public function highlightPhp(string $source): Markup
    {
        if (trim($source) === '') {
            return new Markup('', 'UTF-8');
        }

        $syntheticOpenTag = !str_contains($source, '<?');
        $html = '';
        $tokens = token_get_all($syntheticOpenTag ? "<?php\n" . $source : $source, TOKEN_PARSE);

        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $html .= $this->renderTextFragment($token, $this->classForOperator($token));
                continue;
            }

            [$id, $text] = $token;
            if ($syntheticOpenTag && $index === 0 && in_array($id, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO], true)) {
                continue;
            }
            $class = $this->classForToken($tokens, $index, $id, $text);
            $html .= $this->renderTextFragment($text, $class);
        }

        return new Markup($html, 'UTF-8');
    }

    public function highlightPhpLines(string $source): Markup
    {
        if ($source === '') {
            return new Markup('', 'UTF-8');
        }

        $syntheticOpenTag = !str_contains($source, '<?');
        $tokens = token_get_all($syntheticOpenTag ? "<?php\n" . $source : $source, TOKEN_PARSE);
        $lines = [''];

        foreach ($tokens as $index => $token) {
            if (is_string($token)) {
                $this->appendTextToLines($lines, $token, $this->classForOperator($token));
                continue;
            }

            [$id, $text] = $token;
            if ($syntheticOpenTag && $index === 0 && in_array($id, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO], true)) {
                continue;
            }

            $this->appendTextToLines($lines, $text, $this->classForToken($tokens, $index, $id, $text));
        }

        $html = '';
        foreach ($lines as $index => $lineHtml) {
            $html .= sprintf(
                '<span class="code-block__line"><span class="code-block__line-number" aria-hidden="true">%d</span><span class="code-block__line-code">%s</span></span>',
                $index + 1,
                $lineHtml,
            );
        }

        return new Markup($html, 'UTF-8');
    }

    /**
     * @param array<int, array{0:int,1:string,2?:int}|string> $tokens
     */
    private function classForToken(array $tokens, int $index, int $id, string $text): ?string
    {
        if ($this->isTokenNamed($id, self::KEYWORD_TOKEN_NAMES)) {
            return 'code-token code-token--keyword';
        }

        if ($this->isTokenNamed($id, self::LITERAL_TOKEN_NAMES)) {
            return 'code-token code-token--literal';
        }

        return match ($id) {
            T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG => 'code-token code-token--tag',
            T_ATTRIBUTE => 'code-token code-token--attribute-marker',
            T_COMMENT, T_DOC_COMMENT => 'code-token code-token--comment',
            T_VARIABLE => 'code-token code-token--variable',
            T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE => 'code-token code-token--string',
            T_LNUMBER, T_DNUMBER => 'code-token code-token--number',
            T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NAME_RELATIVE => 'code-token code-token--name',
            T_STRING => $this->classifyStringToken($tokens, $index, $text),
            T_WHITESPACE => null,
            default => null,
        };
    }

    /**
     * @param array<int, array{0:int,1:string,2?:int}|string> $tokens
     */
    private function classifyStringToken(array $tokens, int $index, string $text): string
    {
        $lower = strtolower($text);

        if (in_array($lower, ['true', 'false', 'null'], true)) {
            return 'code-token code-token--literal';
        }

        if (in_array($lower, self::BUILTIN_TYPE_NAMES, true)) {
            return 'code-token code-token--type';
        }

        $previousTokenId = $this->previousNonWhitespaceTokenId($tokens, $index);
        if ($previousTokenId === T_FUNCTION) {
            return 'code-token code-token--function';
        }

        $nextTokenText = $this->nextNonWhitespaceTokenText($tokens, $index);
        if ($nextTokenText === '(') {
            return 'code-token code-token--function';
        }

        if ($text !== '' && ctype_upper($text[0])) {
            return 'code-token code-token--type';
        }

        return 'code-token code-token--identifier';
    }

    private function classForOperator(string $text): ?string
    {
        if (trim($text) === '') {
            return null;
        }

        return match ($text) {
            '(', ')', '[', ']', '{', '}', ',', ';' => 'code-token code-token--punctuation',
            default => 'code-token code-token--operator',
        };
    }

    /**
     * @param list<string> $tokenNames
     */
    private function isTokenNamed(int $id, array $tokenNames): bool
    {
        return in_array(token_name($id), $tokenNames, true);
    }

    /**
     * @param array<int, array{0:int,1:string,2?:int}|string> $tokens
     */
    private function previousNonWhitespaceTokenId(array $tokens, int $index): int|string|null
    {
        for ($cursor = $index - 1; $cursor >= 0; $cursor--) {
            $token = $tokens[$cursor];

            if (is_string($token)) {
                if (trim($token) === '') {
                    continue;
                }

                return $token;
            }

            if ($token[0] === T_WHITESPACE) {
                continue;
            }

            return $token[0];
        }

        return null;
    }

    /**
     * @param array<int, array{0:int,1:string,2?:int}|string> $tokens
     */
    private function nextNonWhitespaceTokenText(array $tokens, int $index): ?string
    {
        for ($cursor = $index + 1, $count = count($tokens); $cursor < $count; $cursor++) {
            $token = $tokens[$cursor];

            if (is_string($token)) {
                if (trim($token) === '') {
                    continue;
                }

                return $token;
            }

            if ($token[0] === T_WHITESPACE) {
                continue;
            }

            return $token[1];
        }

        return null;
    }

    private function renderTextFragment(string $text, ?string $class): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($class === null) {
            return $escaped;
        }

        return sprintf('<span class="%s">%s</span>', $class, $escaped);
    }

    /**
     * @param array<int, string> $lines
     */
    private function appendTextToLines(array &$lines, string $text, ?string $class): void
    {
        $parts = preg_split("/(\r\n|\n|\r)/", $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$text];

        foreach ($parts as $part) {
            if ($part === "\n" || $part === "\r" || $part === "\r\n") {
                $lines[] = '';
                continue;
            }

            $lines[array_key_last($lines)] .= $this->renderTextFragment($part, $class);
        }
    }
}
