<?php

declare(strict_types=1);

namespace App\Application\Payload\Routing;

use App\Application\Resource\Page\BasicPageResource;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/catalog',
    methods: ['GET'],
    responseWith: BasicPageResource::class,
    produces: ['text/html'],
)]
final class BasicRoutePayload
{
}
