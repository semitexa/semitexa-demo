<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
    path: 'env::DEMO_BASIC_ROUTE_PATH::/demo/routing/basic',
    methods: ['GET'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Basic Route',
    slug: 'basic',
    summary: 'Define a route with one attribute — no XML, no YAML, no config files.',
    order: 1,
    highlights: ['#[AsPayload]', 'env::ROUTE_PATH', 'responseWith', 'TypedHandlerInterface'],
    entryLine: 'Define a route with one attribute — and even the path can move through .env without touching PHP code.',
    learnMoreLabel: 'See the code →',
    deepDiveLabel: 'How route compilation works →',
)]
class BasicRoutePayload
{
}
