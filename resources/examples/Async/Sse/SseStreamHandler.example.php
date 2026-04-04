<?php

declare(strict_types=1);

namespace App\Application\Handler\Events;

use App\Application\Payload\Events\ActivityStreamPayload;
use App\Application\Resource\Sse\ActivityStreamResource;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ActivityStreamPayload::class, resource: ActivityStreamResource::class)]
final class SseStreamHandler implements TypedHandlerInterface
{
    public function handle(ActivityStreamPayload $payload, ActivityStreamResource $resource): ActivityStreamResource
    {
        return $resource->stream('catalog.activity');
    }
}
