<?php

declare(strict_types=1);

namespace App\Application\Payload\Api;

use App\Application\Resource\Page\ApiSchemaPageResource;
use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;

#[PublicEndpoint]
#[AsPayload(
    path: '/docs/api/schema',
    methods: ['GET'],
    responseWith: ApiSchemaPageResource::class,
)]
final class ApiSchemaDiscoveryPayload
{
}
