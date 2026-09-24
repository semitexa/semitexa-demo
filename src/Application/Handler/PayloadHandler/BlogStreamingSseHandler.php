<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogStreamingSsePayload;
use Semitexa\Demo\Application\Resource\Response\BlogStreamingSseResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: BlogStreamingSsePayload::class, resource: BlogStreamingSseResource::class)]
final class BlogStreamingSseHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoBlogCatalog $blog;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BlogStreamingSsePayload $payload, BlogStreamingSseResource $resource): BlogStreamingSseResource
    {
        $article = $this->blog->streamingSseArticle();

        return $resource
            ->pageTitle($article['title'] . ' | Semitexa Blog')
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
