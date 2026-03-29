<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantConfigResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/config',
    methods: ['GET'],
    responseWith: DemoTenantConfigResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'platform',
    title: 'Per-Tenant Configuration',
    slug: 'tenancy-config',
    summary: 'Three demo tenants with distinct branding — switch tenant, everything changes without if/else.',
    order: 22,
    highlights: ['TenantContext::getLayer()', 'ThemeLayer', 'DemoTenantConfig', 'featureFlags'],
    entryLine: 'Acme (blue, serif) · Globex (green, modern) · Initech (orange, minimal).',
    learnMoreLabel: 'See branding config →',
    deepDiveLabel: 'TenantContext layer resolution →',
)]
class TenantConfigPayload
{
    protected ?string $tenant = null;

    public function getTenant(): ?string { return $this->tenant; }
    public function setTenant(?string $tenant): void { $this->tenant = $tenant; }
}
