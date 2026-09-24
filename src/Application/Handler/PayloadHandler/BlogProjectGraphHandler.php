<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogProjectGraphPayload;
use Semitexa\Demo\Application\Resource\Response\BlogProjectGraphResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: BlogProjectGraphPayload::class, resource: BlogProjectGraphResource::class)]
final class BlogProjectGraphHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoBlogCatalog $blog;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BlogProjectGraphPayload $payload, BlogProjectGraphResource $resource): BlogProjectGraphResource
    {
        $article = $this->blog->projectGraphArticle();

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
