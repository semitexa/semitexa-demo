<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantLayersResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy-layers',
    methods: ['GET'],
    responseWith: DemoTenantLayersResource::class,
    produces: ['application/json', 'text/html'],
)]
class TenantLayersPayload
{
}
