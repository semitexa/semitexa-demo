<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/di/factory',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'di',
    title: 'Factory Injection',
    slug: 'factory',
    summary: 'On-demand creation stays explicit — lazy instances without falling back to service locator habits.',
    order: 4,
    highlights: ['#[InjectAsFactory]', 'closed-world selection', 'on-demand', 'lazy instantiation'],
    entryLine: 'On-demand creation stays explicit — lazy instances without falling back to service locator habits.',
    learnMoreLabel: 'See factory injection →',
    deepDiveLabel: 'Lazy instantiation patterns →',
)]
class FactoryInjectionPayload
{
}
