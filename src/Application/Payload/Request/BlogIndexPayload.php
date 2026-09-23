<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogIndexResource;

#[AsPublicPayload(responseWith: BlogIndexResource::class, produces: ['text/html'], path: '/blog', methods: ['GET'])]
final class BlogIndexPayload
{
}
