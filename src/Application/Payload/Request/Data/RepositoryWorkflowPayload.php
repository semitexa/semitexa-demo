<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/repository-workflow',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'data',
    title: 'Repository Workflow',
    slug: 'repository-workflow',
    summary: 'The canonical Semitexa path: handlers depend on repository contracts, repositories return domain models, and persistence resources stay behind the boundary.',
    order: 1,
    highlights: ['repository contract', 'domain model', 'DomainMappable', '#[SatisfiesRepositoryContract]', 'fetchOne()'],
    entryLine: 'The demo should sell the canon: business code speaks domain language, and ORM resources stay inside the persistence layer.',
    learnMoreLabel: 'See the canonical flow →',
    deepDiveLabel: 'Where resource reads still belong →',
)]
final class RepositoryWorkflowPayload
{
}
