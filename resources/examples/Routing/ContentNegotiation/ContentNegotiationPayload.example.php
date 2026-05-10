<?php

declare(strict_types=1);

namespace App\Application\Payload\Routing;

use App\Application\Resource\Page\ProductCollectionPageResource;
use Semitexa\Core\Attribute\AsPublicPayload;

#[AsPublicPayload(
    path: '/products',
    methods: ['GET'],
    responseWith: ProductCollectionPageResource::class,
    produces: ['text/html', 'application/json'],
)]
final class ContentNegotiationPayload
{
}
