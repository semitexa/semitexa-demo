<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Data;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/data/repository-workflow',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
final class RepositoryWorkflowPayload
{
}
