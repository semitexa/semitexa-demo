<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\GetStarted;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/get-started/module-structure',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
final class ModuleStructurePayload
{
}
