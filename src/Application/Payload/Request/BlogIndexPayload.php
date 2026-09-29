<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\BlogIndexResource;
use Semitexa\Ssr\Application\Service\Seo\Sitemap\NotInSitemap;

#[AsPublicPayload(responseWith: BlogIndexResource::class, produces: ['text/html'], path: '/blog', methods: ['GET'])]
#[NotInSitemap]
final class BlogIndexPayload
{
}
