<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/cli/ai-tooling',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'cli',
    title: 'AI Tooling Surface',
    slug: 'ai-tooling',
    summary: 'Semitexa exposes AI-facing commands as explicit CLI contracts: capabilities, skills, log access, and a local assistant entrypoint.',
    order: 1,
    highlights: ['ai:capabilities', 'ai:skills', 'logs:app', 'ai', '--json'],
    entryLine: 'If the framework wants to be AI-native, the console surface has to be machine-readable and operationally safe, not just human-friendly.',
    learnMoreLabel: 'See the AI command surface →',
    deepDiveLabel: 'What makes it agent-friendly →',
)]
final class AiToolingPayload
{
}
