<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\Platform\DemoTenantLayersResource;

#[AsPublicPayload(
    path: '/demo/platform/tenancy-layers',
    methods: ['GET'],
    responseWith: DemoTenantLayersResource::class,
    produces: ['text/html', 'application/json'],
)]
class TenantLayersPayload
{
}
