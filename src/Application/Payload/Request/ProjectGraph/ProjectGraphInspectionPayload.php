<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\ProjectGraph;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/project-graph/inspection',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'project-graph',
    title: 'Inspecting the Graph',
    slug: 'inspection',
    summary: 'Use the graph to explore modules, dependencies, cross-module edges, and AI-relevant capabilities without reconstructing the architecture by hand.',
    order: 2,
    highlights: ['ai:review-graph:show', 'ai:review-graph:query', 'cross-module edges', 'capability manifest'],
    entryLine: 'Once the graph exists, the terminal stops being a place for blind searching and becomes a place for direct architectural questions.',
    learnMoreLabel: 'See the inspection workflows →',
    deepDiveLabel: 'What this saves during real project work →',
)]
final class ProjectGraphInspectionPayload
{
}
