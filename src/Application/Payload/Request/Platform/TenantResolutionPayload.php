<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantResolutionResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/resolution',
    methods: ['GET'],
    responseWith: DemoTenantResolutionResource::class,
)]
#[DemoFeature(
    section: 'platform',
    title: 'Tenant Resolution Strategies',
    slug: 'tenancy-resolution',
    summary: 'Interactive panel with 4 tabs — header, subdomain, path, and query param strategies.',
    order: 20,
    highlights: ['TenantResolverChain', 'HeaderStrategy', 'SubdomainStrategy', 'PathStrategy', 'QueryParamStrategy'],
    entryLine: 'Same URL, different world — see which strategy resolved the tenant context.',
    learnMoreLabel: 'See resolution chain →',
    deepDiveLabel: 'TenantResolverChain internals →',
)]
class TenantResolutionPayload
{
    protected ?string $tab = null;

    public function getTab(): ?string { return $this->tab; }
    public function setTab(?string $tab): void { $this->tab = $tab; }
}
