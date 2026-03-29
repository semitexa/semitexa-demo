<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/schema-sync',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'data',
    title: 'Schema Sync, Not Migration Churn',
    slug: 'schema-sync',
    summary: 'Semitexa creates SQL only when the real schema changed, blocks destructive drops by default, and logs the exact DDL plan as SQL and JSON.',
    order: 4,
    highlights: ['orm:sync', '--dry-run', '--allow-destructive', 'two-phase drop', 'AuditLogger'],
    entryLine: 'You do not hand-write busywork migrations all day. The ORM derives the plan, blocks dangerous drops by default, and records the exact SQL it ran.',
    learnMoreLabel: 'See the sync plan →',
    deepDiveLabel: 'Why destructive changes are delayed →',
)]
final class SchemaSyncPayload
{
}
