<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
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
    summary: 'Request-scoped services get a fresh clone per request — safe state without global mutation.',
    order: 2,
    highlights: ['#[InjectAsMutable]', 'request-scoped', 'clone', 'state isolation'],
    entryLine: 'Request-scoped services get a fresh clone per request — safe state without global mutation.',
    learnMoreLabel: 'See mutable injection →',
    deepDiveLabel: 'Clone lifecycle under the hood →',
)]
class MutableInjectionPayload
{
}
