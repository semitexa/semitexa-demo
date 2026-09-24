<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogStreamingSseResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;

#[AsPublicPayload(responseWith: BlogStreamingSseResource::class, produces: ['text/html'], path: DemoBlogCatalog::STREAMING_SSE_PATH, methods: ['GET'])]
final class BlogStreamingSsePayload
{
}
