<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/cli/runtime-maintenance',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'cli',
    title: 'Runtime Maintenance',
    slug: 'runtime-maintenance',
    summary: 'Reload workers, clear stale cache, sync registries, lint architecture rules, and probe handler wiring without reaching for ad-hoc shell scripts.',
    order: 5,
    highlights: ['server:reload', 'cache:clear', 'registry:sync', 'semitexa:lint:*', 'test:handler'],
    entryLine: 'Strong CLI does not stop at code generation. It also gives operators and developers a disciplined way to refresh, validate, and diagnose a live Semitexa runtime.',
    learnMoreLabel: 'See the maintenance workflow →',
    deepDiveLabel: 'How to use this safely in practice →',
)]
final class RuntimeMaintenancePayload
{
}
