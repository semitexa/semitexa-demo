<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ErrorEnvelopeResource;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Core\Attributes\AsPayload;

#[ExternalApi]
#[AsPayload(
    path: '/api/demo/errors/{scenario}',
    methods: ['GET'],
    responseWith: ErrorEnvelopeResource::class,
    produces: ['application/json'],
)]
final class ApiErrorTriggerPayload
{
    protected string $scenario = 'not-found';
}
