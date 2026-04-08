<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
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
    summary: 'Declare parent and child links on the resource itself, then read typed relations from the handler.',
    order: 8,
    highlights: ['#[HasMany]', '#[BelongsTo]', 'foreignKey', 'typed relations', 'batch loading'],
    entryLine: 'Declare parent and child links on the resource itself, then read typed relations from the handler.',
    learnMoreLabel: 'See the relation attributes →',
    deepDiveLabel: 'How handler reads relations →',
)]
class RelationsPayload
{
}
