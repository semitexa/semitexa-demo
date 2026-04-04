<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Api\ErrorEnvelopeResource;
use Semitexa\Api\Attributes\ExternalApi;
use Semitexa\Core\Attribute\AsPayload;

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

    public function setScenario(string $scenario): void
    {
        $this->scenario = $scenario;
    }
}
