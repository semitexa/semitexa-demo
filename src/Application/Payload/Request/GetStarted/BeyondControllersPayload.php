<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/get-started/beyond-controllers',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'get-started',
    title: 'Beyond Controllers',
    slug: 'beyond-controllers',
    summary: 'See why controller-first HTTP design collapses transport, use case, and rendering into one unstable class and why Semitexa keeps them apart.',
    order: 4,
    highlights: ['Payload DTO', 'TypedHandlerInterface', 'Resource DTO', 'controller-free route contract'],
    entryLine: 'Controllers were a useful transition pattern, but once the system grows they turn one class into route config, input mapper, validator, use-case shell, and response assembler all at once.',
    learnMoreLabel: 'See why controllers stop scaling →',
    deepDiveLabel: 'How the Semitexa split stays reviewable →',
)]
final class BeyondControllersPayload
{
}
