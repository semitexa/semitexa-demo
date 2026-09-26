<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogProjectGraphPayload;
use Semitexa\Demo\Application\Resource\Response\BlogProjectGraphResource;

#[AsPayloadHandler(payload: BlogProjectGraphPayload::class, resource: BlogProjectGraphResource::class)]
final class BlogProjectGraphHandler implements TypedHandlerInterface
{
    public function handle(BlogProjectGraphPayload $payload, BlogProjectGraphResource $resource): BlogProjectGraphResource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog/project-graph-semitexa');
        $resource->setContent('');

        return $resource;
    }
}
