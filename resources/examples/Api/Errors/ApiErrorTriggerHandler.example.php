<?php

declare(strict_types=1);

namespace App\Application\Handler\Api;

use App\Application\Exception\Api\DemoApiNotFoundException;
use App\Application\Payload\Api\ApiErrorTriggerPayload;
use App\Application\Resource\Api\ErrorEnvelopeResource;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ApiErrorTriggerPayload::class, resource: ErrorEnvelopeResource::class)]
final class ApiErrorTriggerHandler implements TypedHandlerInterface
{
    public function handle(ApiErrorTriggerPayload $payload, ErrorEnvelopeResource $resource): ErrorEnvelopeResource
    {
        throw new DemoApiNotFoundException('Product', 'demo-product');
    }
}
