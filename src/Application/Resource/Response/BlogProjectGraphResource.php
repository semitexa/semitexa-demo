<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlResponse;

#[AsResource(handle: 'demo_blog_project_graph', template: '@project-layouts-semitexa-demo/pages/blog-project-graph.html.twig')]
final class BlogProjectGraphResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    /** @param array<string, string> $article */
    public function withArticle(array $article): self
    {
        return $this->with('article', $article);
    }
}
