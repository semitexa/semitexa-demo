<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\ProjectGraph;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/project-graph/overview',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'project-graph',
    title: 'Project Graph Overview',
    slug: 'overview',
    summary: 'Build a live structural map of the Semitexa codebase so both engineers and AI agents can start from real architecture instead of guesswork.',
    order: 1,
    highlights: ['ai:review-graph:generate', 'ai:review-graph:stats', 'ai:review-graph:capabilities', 'incremental graph'],
    entryLine: 'This is the fastest way to turn a large Semitexa codebase from “I need to search around first” into a queryable, reviewable system map.',
    learnMoreLabel: 'See the quick start →',
    deepDiveLabel: 'Why this becomes a real engineering advantage →',
)]
final class ProjectGraphOverviewPayload
{
}
