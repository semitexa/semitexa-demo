<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogSseResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;

#[AsPublicPayload(responseWith: BlogSseResource::class, produces: ['text/html'], path: DemoBlogCatalog::SSE_PATH, methods: ['GET'])]
final class BlogSsePayload
{
}
