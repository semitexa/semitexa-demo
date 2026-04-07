<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
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
    summary: 'Depend on contracts, but keep ownership explicit — deterministic substitution instead of runtime magic.',
    order: 5,
    highlights: ['#[SatisfiesServiceContract]', 'module-owned capability', 'closed-world factory', 'deterministic binding'],
    entryLine: 'Depend on contracts, but keep ownership explicit — deterministic substitution instead of runtime magic.',
    learnMoreLabel: 'See contract attributes →',
    deepDiveLabel: 'How contract resolution works →',
)]
class ServiceContractPayload
{
}
