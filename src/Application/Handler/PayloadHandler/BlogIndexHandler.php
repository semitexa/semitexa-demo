<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogIndexPayload;
use Semitexa\Demo\Application\Resource\Response\BlogIndexResource;

#[AsPayloadHandler(payload: BlogIndexPayload::class, resource: BlogIndexResource::class)]
final class BlogIndexHandler implements TypedHandlerInterface
{
    public function handle(BlogIndexPayload $payload, BlogIndexResource $resource): BlogIndexResource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog');
        $resource->setContent('');

        return $resource;
    }
}
