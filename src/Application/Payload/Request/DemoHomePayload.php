<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;

#[AsPublicPayload(
    responseWith: DemoHomeResource::class,
    produces: ['application/json', 'text/html'],
    path: '/',
    methods: ['GET'],
)]
class DemoHomePayload
{
}
