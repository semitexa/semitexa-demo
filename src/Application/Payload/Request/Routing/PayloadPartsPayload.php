<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Routing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/routing/payload-parts',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'routing',
    title: 'Payload Parts',
    slug: 'payload-parts',
    summary: 'One module owns the route, another module can extend the same payload contract without forking or reopening the base class.',
    order: 4,
    highlights: ['#[AsPayloadPart]', 'trait composition', 'module extension', 'one payload boundary'],
    entryLine: 'A payload can stay the single trusted boundary even when multiple modules need to extend it.',
    learnMoreLabel: 'See modular composition →',
    deepDiveLabel: 'How wrapper composition works →',
)]
final class PayloadPartsPayload
{
}
