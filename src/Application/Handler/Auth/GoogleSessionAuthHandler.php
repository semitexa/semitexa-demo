<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\Auth;

use Semitexa\Auth\Attribute\AsAuthHandler;
use Semitexa\Auth\Handler\AuthHandlerInterface;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Auth\AuthResult;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;

#[AsService]
#[AsAuthHandler(priority: -20)]
final class GoogleSessionAuthHandler implements AuthHandlerInterface
{
    private const string DEFAULT_ROLE = 'viewer';

    #[InjectAsMutable]
    protected ?SessionInterface $session = null;

    public function handle(object $payload): ?AuthResult
    {
        if ($this->session === null) {
            return null;
        }

        $segment = $this->session->getPayload(
            GoogleAuthSessionSegment::class
        );

        $identity = $segment->getIdentity();

        if ($identity === null || !$segment->isAuthenticated()) {
            return null;
        }

        $role = $this->normalizeRole($segment->getDemoRole());

        if ($segment->getDemoRole() === null) {
            $segment->setDemoRole($role);
            $this->session->setPayload($segment);
        }

        $principal = GooglePrincipal::fromSessionIdentity($identity, $role);

        $this->session->set('_auth_user_id', $principal->getId());

        return AuthResult::success($principal);
    }

    private function normalizeRole(?string $role): string
    {
        $role = $role !== null ? trim($role) : '';
        if ($role === '') {
            return self::DEFAULT_ROLE;
        }

        return in_array($role, ['admin', 'editor', 'viewer'], true) ? $role : self::DEFAULT_ROLE;
    }
}
