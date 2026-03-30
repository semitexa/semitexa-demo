<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/cli/describe-commands',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'cli',
    title: 'Project Describe Commands',
    slug: 'describe-commands',
    summary: 'Routes, modules, contracts, and handlers can be described directly from the CLI instead of reverse-engineering the framework graph by hand.',
    order: 4,
    highlights: ['describe:route', 'describe:project', 'routes:list', 'contracts:list', 'semitexa:lint:*'],
    entryLine: 'A mature framework should explain itself under pressure. These commands turn route and container introspection into a first-class debugging surface.',
    learnMoreLabel: 'See the introspection workflow →',
    deepDiveLabel: 'How to use it in real debugging →',
)]
final class DescribeCommandsPayload
{
}
