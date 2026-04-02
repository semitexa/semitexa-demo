<?php

declare(strict_types=1);

namespace App\Auth\Session;

use Semitexa\Core\Session\Attribute\SessionSegment;

#[SessionSegment('browser_auth')]
final class BrowserSessionSegment
{
    private ?string $userId = null;
    private ?string $displayName = null;

    public function isGuest(): bool
    {
        return $this->userId === null;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function requireUserId(): string
    {
        if ($this->userId === null) {
            throw new \LogicException('Session does not contain an authenticated user.');
        }

        return $this->userId;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName;
    }

    public function clear(): void
    {
        $this->userId = null;
        $this->displayName = null;
    }
}
