<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

/** Editorial metadata shared by the blog listing and article page. */
#[AsService]
final class DemoBlogCatalog
{
    public const SSE_PATH = '/blog/server-sent-events-explained';
    public const STREAMING_SSE_PATH = '/blog/streaming-sse-semitexa';
    public const PROJECT_GRAPH_PATH = '/blog/project-graph-semitexa';

    /** @return array{title: string, description: string, path: string, published: string, author: string, category: string} */
    public function projectGraphArticle(): array
    {
        return [
            'title' => 'Project Graph in Semitexa: Map PHP Dependencies Before You Edit',
            'description' => 'Project Graph in Semitexa maps PHP routes, handlers, dependencies and impact. See real CLI queries, AI context, and working Demo workflows.',
            'path' => self::PROJECT_GRAPH_PATH,
            'published' => '2026-09-24',
            'author' => 'Semitexa Team',
            'category' => 'Architecture',
        ];
    }

    /** @return array{title: string, description: string, path: string, published: string, author: string, category: string} */
    public function streamingSseArticle(): array
    {
        return [
            'title' => 'Streaming SSE with Semitexa: Live PHP Updates and HTML',
            'description' => 'Streaming SSE in Semitexa delivers live events and server-rendered HTML over HTTP. See the real PHP demos, shared stream, and production considerations.',
            'path' => self::STREAMING_SSE_PATH,
            'published' => '2026-09-24',
            'author' => 'Semitexa Team',
            'category' => 'Real-time web',
        ];
    }

    /** @return array{title: string, description: string, path: string, published: string, author: string, category: string} */
    public function sseArticle(): array
    {
        return [
            'title' => 'Server-Sent Events Explained: How SSE Works and When to Use It',
            'description' => 'Learn how Server-Sent Events (SSE) work, compare SSE with WebSockets and polling, and explore real-time PHP interfaces with Semitexa Framework.',
            'path' => self::SSE_PATH,
            'published' => '2026-09-23',
            'author' => 'Semitexa Team',
            'category' => 'Real-time web',
        ];
    }

    /** @return list<array{title: string, description: string, path: string, published: string, author: string, category: string}> */
    public function articles(): array
    {
        return [$this->projectGraphArticle(), $this->streamingSseArticle(), $this->sseArticle()];
    }
}
