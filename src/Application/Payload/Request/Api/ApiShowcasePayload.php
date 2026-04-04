<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/rest-api',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'api',
    title: 'REST API',
    slug: 'rest-api',
    summary: 'Classic Semitexa REST endpoints with typed payloads, versioning, and consumer-friendly response shaping.',
    order: 2,
    highlights: ['#[ExternalApi]', '#[ApiVersion]', 'application/ld+json', 'fields', 'expand', 'X-Response-Profile'],
    entryLine: 'If you want clean REST, Semitexa already gives you a strong machine-facing contract without extra ceremony.',
    learnMoreLabel: 'See simple REST payloads →',
    deepDiveLabel: 'Why Semitexa REST stays clean →',
)]
final class ApiShowcasePayload
{
}
