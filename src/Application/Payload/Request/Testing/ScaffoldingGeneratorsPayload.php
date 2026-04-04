<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/cli/scaffolding-generators',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'cli',
    title: 'Scaffolding Generators',
    slug: 'scaffolding-generators',
    summary: 'Scaffold modules, pages, payloads, services, and contracts through commands that already understand Semitexa structure and AI-friendly output modes.',
    order: 2,
    highlights: ['make:module', 'make:page', 'make:payload', 'make:service', 'make:contract', '--llm-hints'],
    entryLine: 'The generator surface matters because it teaches the framework shape by producing the right files, not by asking the developer to remember ceremony.',
    learnMoreLabel: 'See the generator workflow →',
    deepDiveLabel: 'Why this scaffolding is different →',
)]
final class ScaffoldingGeneratorsPayload
{
}
