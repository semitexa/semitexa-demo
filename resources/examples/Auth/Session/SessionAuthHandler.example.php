<?php

declare(strict_types=1);

namespace App\Auth;

use App\Auth\Session\BrowserSessionSegment;
use App\User\UserRepositoryInterface;
use Semitexa\Auth\Attribute\AsAuthHandler;
use Semitexa\Auth\Handler\AuthHandlerInterface;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Auth\AuthResult;
use Semitexa\Core\Session\SessionInterface;

#[AsAuthHandler(priority: 0)]
final class SessionAuthHandler implements AuthHandlerInterface
{
    #[InjectAsMutable]
    protected SessionInterface $session;

    #[InjectAsReadonly]
    protected UserRepositoryInterface $users;

    public function handle(object $payload): ?AuthResult
    {
        $segment = $this->session->getPayload(BrowserSessionSegment::class);
        if ($segment->isGuest()) {
            return null;
        }

        return AuthResult::success(
            $this->users->getById($segment->requireUserId()),
        );
    }
}
