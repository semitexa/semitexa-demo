<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/basic',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'routing',
    title: 'Basic Route',
    slug: 'basic',
    summary: 'Define a route with one attribute — no XML, no YAML, no config files.',
    order: 1,
    highlights: ['#[AsPayload]', 'responseWith', 'TypedHandlerInterface'],
    entryLine: 'Define a route with one attribute — no XML, no YAML, no config files.',
    learnMoreLabel: 'See the code →',
    deepDiveLabel: 'How route compilation works →',
)]
class BasicRoutePayload
{
}
