<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;

#[PublicEndpoint]
#[AsPayload(
    responseWith: DemoHomeResource::class,
    path: '/demo',
    methods: ['GET'],
)]
class DemoHomePayload
{
}
