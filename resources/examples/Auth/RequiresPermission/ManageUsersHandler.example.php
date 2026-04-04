<?php

declare(strict_types=1);

namespace App\Handler\Admin;

use App\Payload\Admin\ManageUsersPayload;
use App\Resource\AdminPageResource;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ManageUsersPayload::class, resource: AdminPageResource::class)]
final class ManageUsersHandler implements TypedHandlerInterface
{
    public function handle(ManageUsersPayload $payload, AdminPageResource $resource): AdminPageResource
    {
        return $resource
            ->withTitle('Users')
            ->withSummary('The framework already enforced users.manage before this code ran.');
    }
}
