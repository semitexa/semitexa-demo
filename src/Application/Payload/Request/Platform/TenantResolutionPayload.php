<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/resolution',
    methods: ['GET'],
    responseWith: DemoTenantResolutionResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'platform',
    title: 'Tenant Context Resolution',
    slug: 'tenancy-resolution',
    summary: 'See how Semitexa resolves the active tenant from subdomain, header, path, or query input before the rest of the platform runs.',
    order: 20,
    highlights: ['TenantResolverChain', 'HeaderStrategy', 'SubdomainStrategy', 'PathStrategy', 'QueryParamStrategy'],
    entryLine: 'The first correct tenant match decides which world the request belongs to.',
    learnMoreLabel: 'See the resolution model →',
    deepDiveLabel: 'Why the chain order matters →',
)]
class TenantResolutionPayload
{
    protected ?string $tab = null;

    public function getTab(): ?string { return $this->tab; }
    public function setTab(?string $tab): void { $this->tab = $tab; }
}
