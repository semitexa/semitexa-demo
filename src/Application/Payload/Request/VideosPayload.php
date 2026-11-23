<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoSectionResource;

#[AsPublicPayload(
    path: '/demo/videos',
    methods: ['GET'],
    responseWith: DemoSectionResource::class,
    produces: ['text/html', 'application/json'],
)]
final class VideosPayload
{
}
