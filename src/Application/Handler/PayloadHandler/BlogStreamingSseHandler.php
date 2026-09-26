<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogStreamingSsePayload;
use Semitexa\Demo\Application\Resource\Response\BlogStreamingSseResource;

#[AsPayloadHandler(payload: BlogStreamingSsePayload::class, resource: BlogStreamingSseResource::class)]
final class BlogStreamingSseHandler implements TypedHandlerInterface
{
    public function handle(BlogStreamingSsePayload $payload, BlogStreamingSseResource $resource): BlogStreamingSseResource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog/streaming-sse-semitexa');
        $resource->setContent('');

        return $resource;
    }
}
