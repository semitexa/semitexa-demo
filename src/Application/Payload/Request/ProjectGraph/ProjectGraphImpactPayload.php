<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\ProjectGraph;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/project-graph/impact',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'project-graph',
    title: 'Impact, Context, and Watch Mode',
    slug: 'impact',
    summary: 'Ask what a change will touch, package focused context for AI, and keep the graph fresh while the codebase evolves.',
    order: 3,
    highlights: ['ai:review-graph:impact', '--context', '--prompt', 'ai:review-graph:watch'],
    entryLine: 'This is where Project Graph stops being an index and becomes a safety tool: impact radius, targeted context, and incremental updates before risky edits.',
    learnMoreLabel: 'See the impact workflow →',
    deepDiveLabel: 'How it reduces risky refactors and vague prompts →',
)]
final class ProjectGraphImpactPayload
{
}
