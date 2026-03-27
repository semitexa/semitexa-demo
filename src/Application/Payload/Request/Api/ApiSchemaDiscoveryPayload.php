<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Api;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/api/schema-discovery',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'api',
    title: 'Schema Discovery',
    slug: 'schema-discovery',
    summary: 'A mini Swagger-style explorer for the live product API contract, schema endpoint, and response shapes.',
    order: 2,
    highlights: ['#[ExternalApi]', 'application/schema+json', 'JSON Schema', 'live explorer'],
    entryLine: 'A machine-facing API should explain its own shape and let you exercise the contract without leaving the demo.',
    learnMoreLabel: 'Inspect the live contract →',
    deepDiveLabel: 'Schema generation notes →',
)]
final class ApiSchemaDiscoveryPayload
{
}
