<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: 'env::DEMO_ENV_ROUTE_OVERRIDE_PATH::/demo/routing/env-route-override',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Env Route Override',
    slug: 'env-route-override',
    summary: 'Keep the payload as the route source of truth while allowing operations to remap the public URL through .env.',
    order: 2,
    highlights: ['env::VAR::/fallback', 'path override', '.env-driven routing', 'same payload boundary'],
    entryLine: 'The route still lives in PHP, but deployment can move the public URL without reopening the payload class.',
    learnMoreLabel: 'See env override pattern →',
    deepDiveLabel: 'How resolved route metadata works →',
)]
final class EnvRouteOverridePayload
{
}
