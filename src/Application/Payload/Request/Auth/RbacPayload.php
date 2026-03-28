<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
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
    summary: 'Role-based access control — assign permissions to roles, assign roles to users.',
    order: 3,
    highlights: ['#[RequiresPermission]', '#[RequiresCapability]', 'RoleInterface', 'permission slugs'],
    entryLine: 'Role-based access control — assign permissions to roles, assign roles to users.',
    learnMoreLabel: 'See the permission model →',
    deepDiveLabel: 'How RBAC resolution works →',
)]
class RbacPayload
{
}
