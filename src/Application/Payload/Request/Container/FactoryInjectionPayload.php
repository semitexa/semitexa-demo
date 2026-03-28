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
    summary: 'On-demand service creation — the factory is shared, but each call produces a new instance.',
    order: 3,
    highlights: ['#[InjectAsFactory]', 'factory callable', 'on-demand', 'lazy instantiation'],
    entryLine: 'On-demand service creation — the factory is shared, but each call produces a new instance.',
    learnMoreLabel: 'See factory injection →',
    deepDiveLabel: 'Lazy instantiation patterns →',
)]
class FactoryInjectionPayload
{
}
