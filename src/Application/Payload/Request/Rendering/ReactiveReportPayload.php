<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Rendering;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/rendering/reactive-report',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class ReactiveReportPayload
{
}
