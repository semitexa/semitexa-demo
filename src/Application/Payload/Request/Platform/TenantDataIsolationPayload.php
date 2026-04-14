<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantIsolationResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/isolation',
    methods: ['GET'],
    responseWith: DemoTenantIsolationResource::class,
    produces: ['application/json', 'text/html'],
)]
class TenantDataIsolationPayload
{
    protected ?string $tenant = null;

    public function getTenant(): ?string { return $this->tenant; }
    public function setTenant(?string $tenant): void { $this->tenant = $tenant; }
}
