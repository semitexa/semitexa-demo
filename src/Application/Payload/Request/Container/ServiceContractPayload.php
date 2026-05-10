<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Container;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/di/contracts',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class ServiceContractPayload
{
}
