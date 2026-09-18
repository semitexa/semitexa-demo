<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/auth/session-payloads',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
)]
final class SessionPayloadsPayload
{
}
