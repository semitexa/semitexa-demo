<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogSsePayload;
use Semitexa\Demo\Application\Resource\Response\BlogSseResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: BlogSsePayload::class, resource: BlogSseResource::class)]
final class BlogSseHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoBlogCatalog $blog;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BlogSsePayload $payload, BlogSseResource $resource): BlogSseResource
    {
        $article = $this->blog->sseArticle();

        return $resource
            ->pageTitle('How Server-Sent Events (SSE) Work — Semitexa Blog')
            ->seoTag('og:title', $article['title'])
            ->seoTag('description', $article['description'])
            ->seoTag('og:description', $article['description'])
            ->seoTag('og:type', 'article')
            ->withDemoShellContext([
                'currentSection' => 'blog',
                'navSections' => $this->catalog->getSections(),
            ])
            ->withArticle($article);
    }
}
