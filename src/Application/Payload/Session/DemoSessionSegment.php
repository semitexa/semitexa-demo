<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Session;

use Semitexa\Core\Session\Attribute\SessionSegment;

/**
 * Session segment for the demo auth showcase.
 * Stores the demo user role chosen on the session auth demo page.
 */
#[SessionSegment('demo_auth')]
final class DemoSessionSegment
{
    private ?string $demoRole = null;
    private ?string $demoUsername = null;
    private int $loginCount = 0;

    public function getDemoRole(): ?string
    {
        return $this->demoRole;
    }

    public function setDemoRole(?string $role): void
    {
        $this->demoRole = $role;
    }

    public function getDemoUsername(): ?string
    {
        return $this->demoUsername;
    }

    public function setDemoUsername(?string $username): void
    {
        $this->demoUsername = $username;
    }

    public function getLoginCount(): int
    {
        return $this->loginCount;
    }

    public function incrementLoginCount(): void
    {
        $this->loginCount++;
    }

    public function isLoggedIn(): bool
    {
        return $this->demoRole !== null;
    }

    public function logout(): void
    {
        $this->demoRole = null;
        $this->demoUsername = null;
    }
}
