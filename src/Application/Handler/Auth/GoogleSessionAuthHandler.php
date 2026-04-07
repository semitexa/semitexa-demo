<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\Auth;

use Semitexa\Auth\Attribute\AsAuthHandler;
use Semitexa\Auth\Handler\AuthHandlerInterface;
use Semitexa\Core\Auth\AuthResult;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;

#[AsAuthHandler(priority: -20)]
final class GoogleSessionAuthHandler implements AuthHandlerInterface
{
    private const DEFAULT_ROLE = 'viewer';

    protected ?SessionInterface $session = null;

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    public function handle(object $payload): ?AuthResult
    {
        if ($this->session === null) {
            return null;
        }

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        if (!$segment->isAuthenticated()) {
            return null;
        }

        $subjectId = $segment->getSubjectId();
        $email = $segment->getEmail();
        $displayName = $segment->getDisplayName() ?: ($email ?? 'Google Account');
        $role = $this->normalizeRole($segment->getDemoRole());

        if ($subjectId === null || $email === null) {
            return null;
        }

        if ($segment->getDemoRole() === null) {
            $segment->setDemoRole($role);
            $this->session->setPayload($segment);
        }

        $principal = new GooglePrincipal(
            subjectId: $subjectId,
            email: $email,
            displayName: $displayName,
            role: $role,
            pictureUrl: $segment->getPictureUrl(),
            hostedDomain: $segment->getHostedDomain(),
            emailVerified: $segment->getEmailVerified(),
        );

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
