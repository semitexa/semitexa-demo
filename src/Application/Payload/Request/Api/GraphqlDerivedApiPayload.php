<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/rest-graphql',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'api',
    title: 'REST + GraphQL',
    slug: 'rest-graphql',
    summary: 'One Semitexa use case can serve both REST and GraphQL without duplicating handler logic into separate resolver classes.',
    order: 4,
    highlights: ['REST + GraphQL', '#[ExposeAsGraphql]', 'shared use case', 'no duplicated logic'],
    entryLine: 'Semitexa lets one use case answer both transports, so teams do not have to choose between REST and GraphQL too early.',
    learnMoreLabel: 'See one use case with two transports →',
    deepDiveLabel: 'Why shared contracts matter here →',
)]
final class GraphqlDerivedApiPayload
{
}
