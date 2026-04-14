<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/di/mutable',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class MutableInjectionPayload
{
}
