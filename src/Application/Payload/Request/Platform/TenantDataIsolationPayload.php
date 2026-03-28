<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantIsolationResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/isolation',
    methods: ['GET'],
    responseWith: DemoTenantIsolationResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'platform',
    title: 'Data Isolation',
    slug: 'tenancy-isolation',
    summary: 'Product listing scoped by tenant — switch tenant, list changes. Zero manual WHERE clauses.',
    order: 21,
    highlights: ['#[TenantScoped]', 'same_storage', 'WHERE tenant_id = ?', 'automatic injection'],
    entryLine: 'Switch tenant — the product list changes instantly. Zero manual WHERE clauses.',
    learnMoreLabel: 'See ORM model →',
    deepDiveLabel: 'WHERE injection mechanics →',
)]
class TenantDataIsolationPayload
{
    protected ?string $tenant = null;

    public function getTenant(): ?string { return $this->tenant; }
    public function setTenant(?string $tenant): void { $this->tenant = $tenant; }
}
