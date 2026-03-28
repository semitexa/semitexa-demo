<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/relations',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'data',
    title: 'Relations',
    slug: 'relations',
    summary: 'Declare associations with attributes — eager loading, N+1 prevention, and nested reads.',
    order: 8,
    highlights: ['#[HasMany]', '#[BelongsTo]', 'RelationWritePolicy', 'AggregateWriteEngine', 'eager loading'],
    entryLine: 'Declare associations with attributes — eager loading, N+1 prevention, and nested reads.',
    learnMoreLabel: 'See the relation attributes →',
    deepDiveLabel: 'How eager loading works →',
)]
class RelationsPayload
{
}
