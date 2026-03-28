<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/showcase',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'api',
    title: 'Consumer Profiles Showcase',
    slug: 'showcase',
    summary: 'One product API, multiple consumers: frontend JSON, JSON-LD crawlers, expanded admin views, and search-oriented collections.',
    order: 1,
    highlights: ['#[ExternalApi]', '#[ApiVersion]', 'application/ld+json', 'fields', 'expand', 'X-Response-Profile'],
    entryLine: 'The same Semitexa product endpoint shifts shape depending on who asks and how they ask.',
    learnMoreLabel: 'See live endpoint contracts →',
    deepDiveLabel: 'API presenter internals →',
)]
final class ApiShowcasePayload
{
}
