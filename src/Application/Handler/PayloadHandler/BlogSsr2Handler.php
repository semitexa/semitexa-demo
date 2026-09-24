<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogSsr2Payload;
use Semitexa\Demo\Application\Resource\Response\BlogSsr2Resource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoSsrQuotePolicy;
use Semitexa\Demo\Application\Service\DemoSsePreviewContext;

#[AsPayloadHandler(payload: BlogSsr2Payload::class, resource: BlogSsr2Resource::class)]
final class BlogSsr2Handler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoBlogCatalog $blog;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected DemoSsrQuotePolicy $quotePolicy;

    #[InjectAsReadonly]
    protected DemoSsePreviewContext $ssePreview;

    public function handle(BlogSsr2Payload $payload, BlogSsr2Resource $resource): BlogSsr2Resource
    {
        $article = $this->blog->ssr2Article();

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
            ->withSsePreview($this->ssePreview->forPage(DemoBlogCatalog::SSR2_PATH . '#server-events'))
            ->withArticle($article)
            ->withQuote($this->quotePolicy->quote($payload->getDelivery()));
    }
}
