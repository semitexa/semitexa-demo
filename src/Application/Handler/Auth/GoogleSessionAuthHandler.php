<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\Auth;

use Semitexa\Auth\Attribute\AsAuthHandler;
use Semitexa\Auth\Handler\AuthHandlerInterface;
use Semitexa\Auth\Session\AuthSessionWriter;
use Semitexa\Core\Attribute\AsService;
use Semitexa\Core\Attribute\ExecutionScoped;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Auth\AuthResult;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;

#[AsService]
#[ExecutionScoped]
#[AsAuthHandler(priority: -20)]
final class GoogleSessionAuthHandler implements AuthHandlerInterface
{
    private const string DEFAULT_ROLE = 'viewer';
    private const string PROVIDER = 'google';

    #[InjectAsMutable]
    protected SessionInterface $session;

    #[InjectAsReadonly]
    protected AuthSessionWriter $authWriter;

    public function handle(object $payload): ?AuthResult
    {
        if (!isset($this->session)) {
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

        $this->authWriter->setAuthenticated(
            $this->session,
            $principal->getId(),
            self::PROVIDER,
        );

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
