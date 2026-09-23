<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogIndexPayload;
use Semitexa\Demo\Application\Resource\Response\BlogIndexResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: BlogIndexPayload::class, resource: BlogIndexResource::class)]
final class BlogIndexHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoBlogCatalog $blog;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BlogIndexPayload $payload, BlogIndexResource $resource): BlogIndexResource
    {
        $description = 'Practical guides to real-time PHP, server-rendered interfaces, and building web applications with Semitexa Framework.';

        return $resource
            ->pageTitle('Semitexa Blog — PHP, SSE and the Real-Time Web')
            ->seoTag('description', $description)
            ->seoTag('og:description', $description)
            ->withDemoShellContext([
                'currentSection' => 'blog',
                'navSections' => $this->catalog->getSections(),
            ])
            ->withArticles($this->blog->articles());
    }
}
