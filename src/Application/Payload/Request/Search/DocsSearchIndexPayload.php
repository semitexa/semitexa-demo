<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Search;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Ssr\Application\Service\Seo\Sitemap\NotInSitemap;

/** The ⌘K search catalog as JSON, fetched by the dialog on first open. */
#[AsPublicPayload(
    responseWith: ResourceResponse::class,
    produces: ['application/json'],
    path: '/demo/docs-search-index',
    methods: ['GET'],
)]
#[NotInSitemap]
final class DocsSearchIndexPayload
{
}
