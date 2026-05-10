<?php

declare(strict_types=1);

namespace App\Payload\Admin;

use App\Resource\AdminPageResource;
use Semitexa\Authorization\Attribute\RequiresPermission;
use Semitexa\Authorization\Attribute\AsProtectedPayload;

#[RequiresPermission('users.manage')]
#[AsProtectedPayload(
    responseWith: AdminPageResource::class,
    path: '/admin/users',
    methods: ['GET'],
)]
final class ManageUsersPayload
{
}
