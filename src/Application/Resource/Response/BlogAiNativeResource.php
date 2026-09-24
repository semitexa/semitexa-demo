<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlResponse;

#[AsResource(handle: 'demo_blog_ai_native', template: '@project-layouts-semitexa-demo/pages/blog-ai-native.html.twig')]
final class BlogAiNativeResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;

    /** @param array<string, string> $article */
    public function withArticle(array $article): self
    {
        return $this->with('article', $article);
    }

    /** @param array<string, string|int|bool> $lab */
    public function withLab(array $lab): self
    {
        return $this->with('lab', $lab);
    }
}
