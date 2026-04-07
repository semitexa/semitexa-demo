<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/machine',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'auth',
    title: 'Machine Auth',
    slug: 'machine',
    summary: 'Service-to-service authentication via Bearer tokens — scoped, revocable, and audited.',
    order: 2,
    highlights: ['MachineAuthHandler', 'Bearer {id}:{secret}', 'MachineCredential', 'scopes', 'revocation'],
    entryLine: 'Service-to-service authentication via Bearer tokens — scoped, revocable, and audited.',
    learnMoreLabel: 'See the Bearer token format →',
    deepDiveLabel: 'Machine auth verification pipeline →',
)]
class MachineAuthPayload
{
}
