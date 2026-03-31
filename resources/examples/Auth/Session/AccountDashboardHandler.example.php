<?php

declare(strict_types=1);

namespace App\Handler\Account;

use App\Payload\Account\DashboardPayload;
use App\Resource\Account\DashboardResource;
use Semitexa\Core\Auth\AuthContextInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: DashboardPayload::class, resource: DashboardResource::class)]
final class AccountDashboardHandler implements TypedHandlerInterface
{
    public function __construct(
        private readonly AuthContextInterface $auth,
    ) {}

    public function handle(DashboardPayload $payload, DashboardResource $resource): DashboardResource
    {
        $user = $this->auth->getUser();

        return $resource
            ->withTitle('My account')
            ->withGreeting('Welcome back, ' . $user->getDisplayName())
            ->withEmail($user->getEmail());
    }
}
