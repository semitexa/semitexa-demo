<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    responseWith: DemoFeatureResource::class,
    path: '/demo/routing/public-endpoint',
    methods: ['GET'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Public Endpoint',
    slug: 'public-endpoint',
    summary: 'Every endpoint is private by default. #[PublicEndpoint] is the explicit opt-in for anonymous access.',
    order: 2,
    highlights: ['#[PublicEndpoint]', 'default private', '401 Unauthorized', 'Authorizer'],
    entryLine: 'Anonymous access is never accidental: without #[PublicEndpoint], Semitexa treats the route as protected.',
    learnMoreLabel: 'See the access contract →',
    deepDiveLabel: 'How the authorizer decides →',
)]
class PublicEndpointPayload
{
}
