<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantQueueResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy-queue',
    methods: ['GET'],
    responseWith: DemoTenantQueueResource::class,
    produces: ['application/json', 'text/html'],
)]
class TenantQueuePayload
{
    protected ?string $tenant = null;

    public function getTenant(): ?string { return $this->tenant; }
    public function setTenant(?string $tenant): void { $this->tenant = $tenant; }
}
