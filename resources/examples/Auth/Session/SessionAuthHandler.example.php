<?php

declare(strict_types=1);

namespace App\Auth;

use App\Auth\Session\BrowserSessionSegment;
use App\User\UserRepositoryInterface;
use Semitexa\Auth\Attribute\AsAuthHandler;
use Semitexa\Auth\Handler\AuthHandlerInterface;
use Semitexa\Core\Auth\AuthResult;
use Semitexa\Core\Session\SessionInterface;

#[AsAuthHandler(priority: 0)]
final class SessionAuthHandler implements AuthHandlerInterface
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function handle(object $payload): ?AuthResult
    {
        $segment = $this->session->getPayload(BrowserSessionSegment::class);
        $userId = $segment->requireUserId();

        return AuthResult::success(
            $this->users->getById($userId),
        );
    }
}
