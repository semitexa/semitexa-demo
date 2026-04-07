<?php

declare(strict_types=1);

namespace App\Application\Payload\Admin;

use App\Application\Resource\Admin\UserListResource;
use Semitexa\Authorization\Attribute\RequiresPermission;
use Semitexa\Core\Attribute\AsPayload;

#[RequiresPermission('users.manage')]
#[AsPayload(
    path: '/admin/users',
    methods: ['GET'],
    responseWith: UserListResource::class,
)]
final class UserListPayload
{
}
