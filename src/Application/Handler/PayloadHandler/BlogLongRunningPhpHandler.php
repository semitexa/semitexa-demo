<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogLongRunningPhpPayload;
use Semitexa\Demo\Application\Resource\Response\BlogLongRunningPhpResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Demo\Application\Service\DemoCatalogService;

#[AsPayloadHandler(payload: BlogLongRunningPhpPayload::class, resource: BlogLongRunningPhpResource::class)]
final class BlogLongRunningPhpHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoBlogCatalog $blog;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    // A scalar on the execution-scoped clone: it must never count other requests.
    private int $handled = 0;

    public function handle(BlogLongRunningPhpPayload $payload, BlogLongRunningPhpResource $resource): BlogLongRunningPhpResource
    {
        $article = $this->blog->longRunningPhpArticle();
        // Each visit must execute the probe, including behind a caching proxy.
        $resource->setHeader('Cache-Control', 'no-store');

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
            ->withArticle($article)
            ->withRuntimeProbe(
                workerPid: (int) getmypid(),
                executionCount: ++$this->handled,
                renderedAt: gmdate('Y-m-d\TH:i:s\Z'),
            );
    }
}
