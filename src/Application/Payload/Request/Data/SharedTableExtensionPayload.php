<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/table-extension',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'data',
    title: 'Shared Table Extension',
    slug: 'table-extension',
    summary: 'Two modules can extend one table independently, and the ORM merges the schema without forcing either side to edit the other.',
    order: 3,
    highlights: ['#[FromTable]', 'SchemaCollector', 'Module isolation', '#[Column]', '#[TenantScoped]'],
    entryLine: 'One module starts the table. Another module adds columns later. Neither one needs to reopen the other module\'s code.',
    learnMoreLabel: 'See both modules side by side →',
    deepDiveLabel: 'Why this is a real ORM advantage →',
)]
final class SharedTableExtensionPayload
{
}
