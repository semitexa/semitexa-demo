<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantQueueResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/queue',
    methods: ['GET'],
    responseWith: DemoTenantQueueResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'platform',
    title: 'Queue Tenant Propagation',
    slug: 'tenancy-queue',
    summary: 'Tenant context travels with queued jobs — _tenant key injected automatically, restored by worker.',
    order: 24,
    highlights: ['TenantAwareJobSerializer', 'wrap()', 'unwrap()', '_tenant key'],
    entryLine: 'Tenant context travels with queued jobs — no manual propagation.',
    learnMoreLabel: 'See serialized payload →',
    deepDiveLabel: 'Worker context restore →',
)]
class TenantQueuePayload
{
    protected ?string $tenant = null;

    public function getTenant(): ?string { return $this->tenant; }
    public function setTenant(?string $tenant): void { $this->tenant = $tenant; }
}
