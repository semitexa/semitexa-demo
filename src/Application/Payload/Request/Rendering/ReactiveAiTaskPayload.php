<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/rendering/reactive-ai',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
)]
#[DemoFeature(
    section: 'rendering',
    title: 'Reactive AI Task',
    slug: 'reactive-ai',
    summary: 'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
    order: 13,
    highlights: ['DemoAiTask', 'stage-by-stage', 'refreshInterval: 2', 'user-triggered → cron pickup'],
    entryLine: 'Submit a task and watch the AI pipeline stages reveal one by one as the cron job processes it.',
    learnMoreLabel: 'See submit form →',
    deepDiveLabel: 'Processor architecture →',
)]
class ReactiveAiTaskPayload
{
}
