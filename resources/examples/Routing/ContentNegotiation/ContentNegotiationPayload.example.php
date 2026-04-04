<?php

declare(strict_types=1);

namespace App\Application\Payload\Routing;

use App\Application\Resource\Page\ProductCollectionPageResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/products',
    methods: ['GET'],
    responseWith: ProductCollectionPageResource::class,
    produces: ['text/html', 'application/json'],
)]
final class ContentNegotiationPayload
{
}
