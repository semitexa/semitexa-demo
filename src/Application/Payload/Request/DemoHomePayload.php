<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoHomeResource;

#[AsPublicPayload(
    responseWith: DemoHomeResource::class,
    produces: ['text/html', 'application/json'],
    path: '/',
    methods: ['GET'],
)]
class DemoHomePayload
{
}
