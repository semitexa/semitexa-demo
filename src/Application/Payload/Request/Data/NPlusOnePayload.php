<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/n-plus-one',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'data',
    title: 'N+1 Without Magic',
    slug: 'n-plus-one',
    summary: 'Semitexa avoids N+1 by using resource slices for the exact columns and relations each screen needs, instead of hiding database traffic behind implicit relation loading.',
    order: 7,
    highlights: ['TableModelRelationLoader', 'resource slice', 'no lazy loading', '#[FromTable]', 'batch relations'],
    entryLine: 'No magic, no lazy loading, no bloated entity graphs. A screen asks for one slice, the ORM hydrates exactly that slice.',
    learnMoreLabel: 'Compare the two ORM styles →',
    deepDiveLabel: 'How Semitexa avoids N+1 →',
)]
final class NPlusOnePayload
{
}
