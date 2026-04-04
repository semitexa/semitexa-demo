<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;

#[PublicEndpoint]
#[AsPayload(
    responseWith: DemoHomeResource::class,
    produces: ['application/json', 'text/html'],
    path: '/',
    methods: ['GET'],
)]
class DemoHomePayload
{
}
