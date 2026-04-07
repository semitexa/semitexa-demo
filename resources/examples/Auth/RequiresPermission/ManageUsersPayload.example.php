<?php

declare(strict_types=1);

namespace App\Payload\Admin;

use App\Resource\AdminPageResource;
use Semitexa\Authorization\Attribute\RequiresPermission;
use Semitexa\Core\Attribute\AsPayload;

#[RequiresPermission('users.manage')]
#[AsPayload(
    responseWith: AdminPageResource::class,
    path: '/admin/users',
    methods: ['GET'],
)]
final class ManageUsersPayload
{
}
