<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Llm;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/llm/execution-flow',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'llm',
    title: 'Execution Flow',
    slug: 'execution-flow',
    summary: 'How a user request becomes a planner decision, a reviewed skill proposal, and finally a real console execution.',
    order: 3,
    highlights: ['Planner', 'PlannerResponse', 'SkillExecutor', 'ConversationSession'],
    entryLine: 'The important part is not that the model can talk. The important part is that the path from intent to execution is structured, inspectable, and policy-gated.',
    learnMoreLabel: 'See the planning flow →',
    deepDiveLabel: 'Where the safety boundaries sit →',
)]
final class LlmExecutionFlowPayload
{
}
