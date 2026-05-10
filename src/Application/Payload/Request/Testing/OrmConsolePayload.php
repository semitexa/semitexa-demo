<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Testing;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/cli/orm-console',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
final class OrmConsolePayload
{
}
