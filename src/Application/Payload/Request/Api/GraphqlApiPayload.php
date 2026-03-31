<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/graphql',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'api',
    title: 'GraphQL API',
    slug: 'graphql',
    summary: 'GraphQL-first Semitexa contracts built with typed payloads and typed output DTOs instead of resolver sprawl.',
    order: 3,
    highlights: ['POST /graphql', '#[ExposeAsGraphql]', 'typed output DTOs', 'GraphQL-first'],
    entryLine: 'If your public API is GraphQL-first, Semitexa still keeps the application layer explicit and typed.',
    learnMoreLabel: 'See a GraphQL-first payload →',
    deepDiveLabel: 'Why this is cleaner than resolver drift →',
)]
final class GraphqlApiPayload
{
}
