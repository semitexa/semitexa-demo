<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogProjectGraphResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Ssr\Application\Service\Seo\Sitemap\NotInSitemap;

#[AsPublicPayload(responseWith: BlogProjectGraphResource::class, produces: ['text/html'], path: DemoBlogCatalog::PROJECT_GRAPH_PATH, methods: ['GET'])]
#[NotInSitemap]
final class BlogProjectGraphPayload
{
}
