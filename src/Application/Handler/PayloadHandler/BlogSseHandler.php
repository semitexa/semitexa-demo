<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogSsePayload;
use Semitexa\Demo\Application\Resource\Response\BlogSseResource;

#[AsPayloadHandler(payload: BlogSsePayload::class, resource: BlogSseResource::class)]
final class BlogSseHandler implements TypedHandlerInterface
{
    public function handle(BlogSsePayload $payload, BlogSseResource $resource): BlogSseResource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog/server-sent-events-explained');
        $resource->setContent('');

        return $resource;
    }
}
