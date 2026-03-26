<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Platform;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Platform\DemoTenantLayersResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/platform/tenancy/layers',
    methods: ['GET'],
    responseWith: DemoTenantLayersResource::class,
)]
#[DemoFeature(
    section: 'platform',
    title: 'Multi-Layer Tenancy',
    slug: 'tenancy-layers',
    summary: 'Organization → Locale → Theme → Environment — four independent layers compose into one TenantContext.',
    order: 23,
    highlights: ['MultilayerTenantResolver', 'OrganizationLayer', 'LocaleLayer', 'ThemeLayer', 'EnvironmentLayer'],
    entryLine: 'Four independent layers compose into one TenantContext — configure each independently.',
    learnMoreLabel: 'See layer diagram →',
    deepDiveLabel: 'MultilayerTenantResolver →',
)]
class TenantLayersPayload
{
}
