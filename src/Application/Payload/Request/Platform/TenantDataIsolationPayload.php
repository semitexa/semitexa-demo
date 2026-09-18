<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\Platform\DemoTenantIsolationResource;

#[AsPublicPayload(
    path: '/demo/platform/tenancy-isolation',
    methods: ['GET'],
    responseWith: DemoTenantIsolationResource::class,
    produces: ['text/html', 'application/json'],
)]
class TenantDataIsolationPayload
{
    protected ?string $tenant = null;

    public function getTenant(): ?string { return $this->tenant; }
    public function setTenant(?string $tenant): void { $this->tenant = $tenant; }
}
