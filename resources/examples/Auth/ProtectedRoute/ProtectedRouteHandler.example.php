<?php

declare(strict_types=1);

namespace App\Application\Handler\Auth;

use App\Application\Payload\Admin\UserListPayload;
use App\Application\Resource\Admin\UserListResource;
use Semitexa\Authorization\Attribute\RequiresPermission;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[RequiresPermission('users.manage')]
#[AsPayloadHandler(payload: UserListPayload::class, resource: UserListResource::class)]
final class ProtectedRouteHandler implements TypedHandlerInterface
{
    public function handle(UserListPayload $payload, UserListResource $resource): UserListResource
    {
        return $resource->fromUsers([]);
    }
}
