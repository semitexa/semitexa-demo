<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/domain-models',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'data',
    title: 'Domain-Level Models',
    slug: 'domain-models',
    summary: 'Semitexa separates persistence resources from business models. Resources map tables; domain models carry behavior and invariants.',
    order: 6,
    highlights: ['DomainMappable', 'fromDomain()', 'toDomain()', '#[SatisfiesRepositoryContract]', 'fetchOneAsResource()'],
    entryLine: 'Resource models exist for persistence. Domain models exist for business behavior. Repositories bridge them instead of collapsing them into one class.',
    learnMoreLabel: 'See both layers side by side →',
    deepDiveLabel: 'How repositories bridge the layers →',
)]
final class DomainModelsPayload
{
}
