<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Llm;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/llm/skills',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'llm',
    title: 'Adding Skills',
    slug: 'skills',
    summary: 'How a console command becomes AI-executable through `#[AsAiSkill]`, metadata policy, and registry discovery.',
    order: 2,
    highlights: ['#[AsAiSkill]', '#[AsCommand]', 'SkillRegistry', 'argumentPolicy'],
    entryLine: 'A Semitexa skill is not a prompt trick. It is a normal console command with explicit AI metadata attached to it.',
    learnMoreLabel: 'See the skill authoring path →',
    deepDiveLabel: 'What metadata the registry extracts →',
)]
final class LlmSkillsPayload
{
}
