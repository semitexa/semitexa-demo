<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/di/contracts',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'di',
    title: 'Service Contracts',
    slug: 'contracts',
    summary: 'Depend on interfaces, not implementations — swap adapters without touching handlers.',
    order: 4,
    highlights: ['#[SatisfiesServiceContract]', '#[SatisfiesRepositoryContract]', 'interface binding', 'swap implementations'],
    entryLine: 'Depend on interfaces, not implementations — swap adapters without touching handlers.',
    learnMoreLabel: 'See contract attributes →',
    deepDiveLabel: 'How contract resolution works →',
)]
class ServiceContractPayload
{
}
