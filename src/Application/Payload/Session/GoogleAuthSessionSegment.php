<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Session;

use Semitexa\Core\Session\Attribute\SessionSegment;

#[SessionSegment('demo_google_auth')]
final class GoogleAuthSessionSegment
{
    private ?string $state = null;
    private ?string $returnTo = null;
    private ?string $demoRole = null;
    private ?string $lastError = null;
    private ?GoogleSessionIdentityPayload $identity = null;

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state !== null && trim($state) !== '' ? trim($state) : null;
    }

    public function getReturnTo(): ?string
    {
        return $this->returnTo;
    }

    public function setReturnTo(?string $returnTo): void
    {
        $this->returnTo = $returnTo !== null && trim($returnTo) !== '' ? trim($returnTo) : null;
    }

    public function getDemoRole(): ?string
    {
        return $this->demoRole;
    }

    public function setDemoRole(?string $demoRole): void
    {
        $demoRole = $demoRole !== null ? trim($demoRole) : null;
        $this->demoRole = $demoRole !== null && $demoRole !== '' ? $demoRole : null;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): void
    {
        $lastError = $lastError !== null ? trim($lastError) : null;
        $this->lastError = $lastError !== null && $lastError !== '' ? $lastError : null;
    }

    public function clearLastError(): void
    {
        $this->lastError = null;
    }

    public function getIdentity(): ?GoogleSessionIdentityPayload
    {
        return $this->identity;
    }

    /**
     * @param array<string, mixed>|GoogleSessionIdentityPayload|null $identity
     */
    public function setIdentity(array|GoogleSessionIdentityPayload|null $identity): void
    {
        if ($identity === null) {
            $this->identity = null;
        } elseif ($identity instanceof GoogleSessionIdentityPayload) {
            $this->identity = $identity;
        } else {
            $this->identity = GoogleSessionIdentityPayload::fromArray($identity);
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->identity !== null
            && $this->identity->getSubjectId() !== ''
            && $this->identity->getEmail() !== '';
    }

    public function clear(): void
    {
        $this->state = null;
        $this->returnTo = null;
        $this->demoRole = null;
        $this->lastError = null;
        $this->identity = null;
    }
}
