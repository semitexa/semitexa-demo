<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/di/mutable',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'di',
    title: 'Mutable Injection',
    slug: 'mutable',
    summary: 'Execution-scoped services get a fresh clone every run — safe state without contaminating the worker.',
    order: 3,
    highlights: ['#[InjectAsMutable]', 'execution-scoped', 'clone', 'state isolation'],
    entryLine: 'Execution-scoped services get a fresh clone every run — safe state without contaminating the worker.',
    learnMoreLabel: 'See mutable injection →',
    deepDiveLabel: 'Clone lifecycle under the hood →',
)]
class MutableInjectionPayload
{
}
