<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Llm;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/llm/overview',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'llm',
    title: 'Overview',
    slug: 'overview',
    summary: 'What `semitexa/llm` adds to the framework and how your project can expose its own CLI skills to the assistant.',
    order: 1,
    highlights: ['#[AsAiSkill]', 'custom skills', 'SkillManifest', 'policy-aware execution'],
    entryLine: 'The LLM module is not “chat pasted onto a framework”. It gives your project a governed way to expose its own commands as AI-usable skills.',
    learnMoreLabel: 'See the module surface →',
    deepDiveLabel: 'Which moving parts matter first →',
)]
final class LlmOverviewPayload
{
}
