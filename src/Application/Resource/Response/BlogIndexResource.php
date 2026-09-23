<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlResponse;

#[AsResource(handle: 'demo_blog', template: '@project-layouts-semitexa-demo/pages/blog-index.html.twig')]
final class BlogIndexResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    /** @param list<array<string, string>> $articles */
    public function withArticles(array $articles): self
    {
        return $this->with('articles', $articles);
    }
}
