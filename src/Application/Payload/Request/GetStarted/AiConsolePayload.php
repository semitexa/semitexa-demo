<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/ai-console',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'AI Console',
    slug: 'ai-console',
    summary: 'Use `bin/semitexa ai` as an alternative CLI entrypoint when you do not want to remember exact command names.',
    order: 5,
    highlights: ['bin/semitexa ai', 'natural-language prompts', 'experimental', 'command translation'],
    entryLine: 'For common maintenance and discovery work you can ask the CLI in plain language instead of recalling every exact command from memory.',
    learnMoreLabel: 'See the AI console flow →',
    deepDiveLabel: 'When to use it and when not to →',
)]
final class AiConsolePayload
{
}
