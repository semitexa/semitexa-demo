<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

/** Editorial metadata shared by the blog listing and article page. */
#[AsService]
final class DemoBlogCatalog
{
    public const SSE_PATH = '/blog/server-sent-events-explained';

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
        return [$this->sseArticle()];
    }
}
