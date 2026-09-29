<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogLongRunningPhpResource;
use Semitexa\Demo\Application\Service\DemoBlogCatalog;
use Semitexa\Ssr\Application\Service\Seo\Sitemap\NotInSitemap;

#[AsPublicPayload(responseWith: BlogLongRunningPhpResource::class, produces: ['text/html'], path: DemoBlogCatalog::LONG_RUNNING_PHP_PATH, methods: ['GET'])]
#[NotInSitemap]
final class BlogLongRunningPhpPayload {}
