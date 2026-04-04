<?php

declare(strict_types=1);

namespace App\Application\Payload\Routing;

use App\Application\Resource\Api\ProductCollectionJsonResource;
use App\Application\Resource\Page\ProductCollectionPageResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/products',
    methods: ['GET'],
    responseWith: ProductCollectionPageResource::class,
    produces: ['text/html', 'application/json'],
    alternates: [
        'application/json' => ProductCollectionJsonResource::class,
    ],
)]
final class ContentNegotiationPayload
{
}
