<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlResponse;

#[AsResource(handle: 'demo_blog_ssr2', template: '@project-layouts-semitexa-demo/pages/blog-redirect.html.twig')]
final class BlogSsr2Resource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    /** @param array<string, mixed> $preview */
    public function withSsePreview(array $preview): self
    {
        return $this->with('ssePreview', $preview);
    }

    /** @param array<string, string> $article */
    public function withArticle(array $article): self
    {
        return $this->with('article', $article);
    }

    /** @param array<string, string> $quote */
    public function withQuote(array $quote): self
    {
        return $this->with('quote', $quote);
    }
}
