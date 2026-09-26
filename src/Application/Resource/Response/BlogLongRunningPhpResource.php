<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Response;

use Semitexa\Core\Attribute\AsResource;
use Semitexa\Core\Contract\ResourceInterface;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlResponse;

#[AsResource(handle: 'demo_blog_long_running_php', template: '@project-layouts-semitexa-demo/pages/blog-redirect.html.twig')]
final class BlogLongRunningPhpResource extends HtmlResponse implements ResourceInterface
{
    use HasDemoShell;
}
