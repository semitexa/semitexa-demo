<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

/** Editorial metadata shared by the blog listing and article page. */
#[AsService]
final class DemoBlogCatalog
{
    public const AUTHOR_NAME = 'Taras Hanych (SyntaxWanderer)';
    public const AUTHOR_URL = 'https://www.linkedin.com/in/syntaxwanderer/';

    public const SSE_PATH = '/blog/server-sent-events-explained';
    public const STREAMING_SSE_PATH = '/blog/streaming-sse-semitexa';
    public const PROJECT_GRAPH_PATH = '/blog/project-graph-semitexa';
    public const SSR2_PATH = '/blog/server-side-rendering-2-0';
    public const AI_NATIVE_PATH = '/blog/ai-native-php-development-semitexa';
    public const LONG_RUNNING_PHP_PATH = '/blog/long-running-php-semitexa';

    /** @return array<string, string> */
    public function longRunningPhpArticle(): array
    {
        return [
            'title' => 'Long-Running PHP: Inside the Semitexa Runtime',
            'description' => 'Built for long-running PHP from day one: explore Semitexa worker lifecycles, coroutine isolation, connection pools and live HTML with a running example.',
            'path' => self::LONG_RUNNING_PHP_PATH,
            'published' => '2026-09-25',
            'author' => self::AUTHOR_NAME,
            'authorUrl' => self::AUTHOR_URL,
            'category' => 'Runtime & Architecture',
            'cardProtocol' => 'PHP / SWOOLE / LIFECYCLE',
            'cardMark' => 'RUN',
            'cardTagline' => 'Built to stay in memory.',
            'linkLabel' => 'Explore the Semitexa runtime',
        ];
    }

    /** @return array<string, string> */
    public function aiNativeArticle(): array
    {
        return [
            'title' => 'AI-Native PHP Development with Semitexa',
            'description' => 'AI-native PHP development with Semitexa: trace a shipping bug through Project Graph, Observatory, and tests. Run the example and verify the result.',
            'path' => self::AI_NATIVE_PATH,
            'published' => '2026-09-24',
            'author' => self::AUTHOR_NAME,
            'authorUrl' => self::AUTHOR_URL,
            'category' => 'AI & Engineering',
            'cardProtocol' => 'GRAPH / TRACE / VERIFY',
            'cardMark' => 'AI',
            'cardTagline' => 'An application an agent can inspect.',
            'linkLabel' => 'Read the AI-native PHP guide',
        ];
    }

    /** @return array<string, string> */
    public function ssr2Article(): array
    {
        return [
            'title' => 'Server Side Rendering 2.0',
            'description' => 'Server Side Rendering 2.0 in PHP: one Twig template, deferred blocks and live SSE events. Explore Semitexa with highlighted code and working demos.',
            'path' => self::SSR2_PATH,
            'published' => '2026-09-24',
            'author' => self::AUTHOR_NAME,
            'authorUrl' => self::AUTHOR_URL,
            'category' => 'Architecture',
            'cardProtocol' => 'PHP / HTML / SSE',
            'cardMark' => 'SSR',
            'cardTagline' => 'One rule. Immediate HTML. Live delivery.',
            'linkLabel' => 'Read the SSR 2.0 guide',
        ];
    }

    /** @return array<string, string> */
    public function projectGraphArticle(): array
    {
        return [
            'title' => 'Project Graph in Semitexa: Map PHP Dependencies Before You Edit',
            'description' => 'Project Graph in Semitexa maps PHP routes, handlers, dependencies and impact. See real CLI queries, AI context, and working Demo workflows.',
            'path' => self::PROJECT_GRAPH_PATH,
            'published' => '2026-09-24',
            'author' => self::AUTHOR_NAME,
            'authorUrl' => self::AUTHOR_URL,
            'category' => 'Architecture',
            'cardProtocol' => 'CODE / dependency graph',
            'cardMark' => 'PG',
            'cardTagline' => 'See the structure before you edit.',
            'linkLabel' => 'Read the Project Graph guide',
        ];
    }

    /** @return array<string, string> */
    public function streamingSseArticle(): array
    {
        return [
            'title' => 'Streaming SSE with Semitexa: Live PHP Updates and HTML',
            'description' => 'Streaming SSE in Semitexa delivers live events and server-rendered HTML over HTTP. See the real PHP demos, shared stream, and production considerations.',
            'path' => self::STREAMING_SSE_PATH,
            'published' => '2026-09-24',
            'author' => self::AUTHOR_NAME,
            'authorUrl' => self::AUTHOR_URL,
            'category' => 'Real-time web',
            'cardProtocol' => 'HTTP / text/event-stream',
            'cardMark' => 'SSE',
            'cardTagline' => 'One connection. A stream of possibilities.',
            'linkLabel' => 'Read the SSE guide',
        ];
    }

    /** @return array<string, string> */
    public function sseArticle(): array
    {
        return [
            'title' => 'Server-Sent Events Explained: How SSE Works and When to Use It',
            'description' => 'Learn how Server-Sent Events (SSE) work, compare SSE with WebSockets and polling, and explore real-time PHP interfaces with Semitexa Framework.',
            'path' => self::SSE_PATH,
            'published' => '2026-09-23',
            'author' => self::AUTHOR_NAME,
            'authorUrl' => self::AUTHOR_URL,
            'category' => 'Real-time web',
            'cardProtocol' => 'HTTP / text/event-stream',
            'cardMark' => 'SSE',
            'cardTagline' => 'One connection. A stream of possibilities.',
            'linkLabel' => 'Read the SSE guide',
        ];
    }

    /** @return list<array<string, string>> */
    public function articles(): array
    {
        return [$this->longRunningPhpArticle(), $this->aiNativeArticle(), $this->ssr2Article(), $this->projectGraphArticle(), $this->streamingSseArticle(), $this->sseArticle()];
    }
}
