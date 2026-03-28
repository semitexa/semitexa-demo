<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/protected',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'auth',
    title: 'Protected Route',
    slug: 'protected',
    summary: 'Add one attribute to any route and the framework enforces access — 403 returned automatically.',
    order: 5,
    highlights: ['#[RequiresPermission]', '#[PublicEndpoint]', 'guard chain', '403 response'],
    entryLine: 'Add one attribute to any route and the framework enforces access — 403 returned automatically.',
    learnMoreLabel: 'See the guard attributes →',
    deepDiveLabel: 'How the guard chain resolves →',
)]
class ProtectedRoutePayload
{
}
