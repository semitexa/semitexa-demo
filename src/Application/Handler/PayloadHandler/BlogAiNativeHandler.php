<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\BlogAiNativePayload;
use Semitexa\Demo\Application\Resource\Response\BlogAiNativeResource;

#[AsPayloadHandler(payload: BlogAiNativePayload::class, resource: BlogAiNativeResource::class)]
final class BlogAiNativeHandler implements TypedHandlerInterface
{
    public function handle(BlogAiNativePayload $payload, BlogAiNativeResource $resource): BlogAiNativeResource
    {
        $resource->disableAutoRender();
        $resource->setStatusCode(301);
        $resource->setHeader('Location', 'https://semitexa.com/blog/ai-native-php-development-semitexa');
        $resource->setContent('');

        return $resource;
    }
}
