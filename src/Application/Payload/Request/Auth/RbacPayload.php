<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/rbac',
    methods: ['GET'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'auth',
    title: 'RBAC',
    slug: 'rbac',
    summary: 'Hybrid RBAC with coarse-grained capabilities, exact permission slugs, and module-owned permission catalogs.',
    order: 3,
    highlights: ['#[RequiresCapability]', '#[RequiresPermission]', 'CapabilityRegistry', 'PermissionProviderInterface'],
    entryLine: 'Semitexa uses a hybrid authorization model: bitmask-backed capabilities for broad checks and slug permissions for fine-grained business rules.',
    learnMoreLabel: 'See the hybrid permission model →',
    deepDiveLabel: 'How grant resolution and module extension work →',
)]
class RbacPayload
{
}
