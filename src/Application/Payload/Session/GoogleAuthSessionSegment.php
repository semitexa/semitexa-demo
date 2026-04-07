<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Session;

use Semitexa\Core\Session\Attribute\SessionSegment;

#[SessionSegment('demo_google_auth')]
final class GoogleAuthSessionSegment
{
    private ?string $state = null;
    private ?string $returnTo = null;
    private ?string $subjectId = null;
    private ?string $email = null;
    private ?string $displayName = null;
    private ?string $pictureUrl = null;
    private ?string $hostedDomain = null;
    private bool $emailVerified = false;
    private ?string $demoRole = null;
    private ?string $lastError = null;

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

    public function getSubjectId(): ?string
    {
        return $this->subjectId;
    }

    public function setSubjectId(?string $subjectId): void
    {
        $this->subjectId = $subjectId !== null && trim($subjectId) !== '' ? trim($subjectId) : null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email !== null && trim($email) !== '' ? trim($email) : null;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): void
    {
        $this->displayName = $displayName !== null && trim($displayName) !== '' ? trim($displayName) : null;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public function setPictureUrl(?string $pictureUrl): void
    {
        $this->pictureUrl = $pictureUrl !== null && trim($pictureUrl) !== '' ? trim($pictureUrl) : null;
    }

    public function getHostedDomain(): ?string
    {
        return $this->hostedDomain;
    }

    public function setHostedDomain(?string $hostedDomain): void
    {
        $this->hostedDomain = $hostedDomain !== null && trim($hostedDomain) !== '' ? trim($hostedDomain) : null;
    }

    public function getEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool|int|string|null $emailVerified): void
    {
        $this->emailVerified = filter_var($emailVerified, FILTER_VALIDATE_BOOL);
    }

    public function isAuthenticated(): bool
    {
        return $this->subjectId !== null && $this->email !== null;
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

    public function clear(): void
    {
        $this->state = null;
        $this->returnTo = null;
        $this->subjectId = null;
        $this->email = null;
        $this->displayName = null;
        $this->pictureUrl = null;
        $this->hostedDomain = null;
        $this->emailVerified = false;
        $this->demoRole = null;
        $this->lastError = null;
    }
}
