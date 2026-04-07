<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/base-tenant',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'Base Tenant',
    slug: 'base-tenant',
    summary: 'Define the first tenant through environment variables and resolve it through a real local host.',
    order: 3,
    highlights: ['TENANT_ACME_NAME', 'TENANT_ACME_STATUS', 'TENANT_ACME_DOMAIN', 'DomainStrategy'],
    entryLine: 'The first tenant is configuration, not ceremony: define it in `.env`, register the host, restart, and open the tenant like a real product surface.',
    learnMoreLabel: 'See the tenant bootstrap flow →',
    deepDiveLabel: 'How Semitexa resolves that tenant →',
)]
final class BaseTenantPayload
{
}
