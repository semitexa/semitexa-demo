<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/requires-permission',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'auth',
    title: 'Requires Permission',
    slug: 'requires-permission',
    summary: 'Declare one permission slug on the payload and let the framework enforce it before your handler runs.',
    order: 4,
    highlights: ['#[RequiresPermission]', '401 Unauthorized', '403 Forbidden', 'guard chain'],
    entryLine: 'Access control should be declarative: the payload names the required permission, and the framework enforces it automatically.',
    learnMoreLabel: 'See the guarded payload →',
    deepDiveLabel: 'How permission checks resolve →',
)]
class RequiresPermissionPayload
{
}
