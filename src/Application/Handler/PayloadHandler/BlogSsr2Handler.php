<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogSsr2Payload;
use Semitexa\Demo\Application\Resource\Response\BlogSsr2Resource;

#[AsPayloadHandler(payload: BlogSsr2Payload::class, resource: BlogSsr2Resource::class)]
final class BlogSsr2Handler implements TypedHandlerInterface
{
    public function handle(BlogSsr2Payload $payload, BlogSsr2Resource $resource): BlogSsr2Resource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog/server-side-rendering-2-0');
        $resource->setContent('');

        return $resource;
    }
}
