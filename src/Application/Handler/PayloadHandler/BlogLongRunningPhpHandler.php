<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogLongRunningPhpPayload;
use Semitexa\Demo\Application\Resource\Response\BlogLongRunningPhpResource;

#[AsPayloadHandler(payload: BlogLongRunningPhpPayload::class, resource: BlogLongRunningPhpResource::class)]
final class BlogLongRunningPhpHandler implements TypedHandlerInterface
{
    public function handle(BlogLongRunningPhpPayload $payload, BlogLongRunningPhpResource $resource): BlogLongRunningPhpResource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog/long-running-php-semitexa');
        $resource->setContent('');

        return $resource;
    }
}
