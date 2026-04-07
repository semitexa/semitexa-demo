<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/resource-dtos',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Resource DTOs',
    slug: 'resource-dtos',
    summary: 'A Resource DTO is the one typed source of presentation data: handlers shape it once, templates consume it everywhere, and no view has to dissect random arrays.',
    order: 1,
    highlights: ['#[AsResource]', 'HtmlResponse', 'with*() methods', 'typed view data', 'auto render'],
    entryLine: 'Real separation means templates receive one explicit response object, not loose arrays and last-minute data surgery.',
    learnMoreLabel: 'See the response boundary →',
    deepDiveLabel: 'How the resource pipeline works →',
)]
final class ResourceDtoPayload
{
}
