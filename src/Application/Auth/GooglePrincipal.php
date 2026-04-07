<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Auth;

use Semitexa\Core\Auth\AuthenticatableInterface;

final readonly class GooglePrincipal implements AuthenticatableInterface
{
    public function __construct(
        public string $subjectId,
        public string $email,
        public string $displayName,
        public string $role = 'viewer',
        public ?string $pictureUrl = null,
        public ?string $hostedDomain = null,
        public bool $emailVerified = false,
    ) {}

    public function getId(): string
    {
        return 'google:' . $this->subjectId . ':' . $this->role;
    }

    public function getAuthIdentifierName(): string
    {
        return 'google_subject';
    }

    public function getAuthIdentifier(): string
    {
        return $this->subjectId;
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public function getHostedDomain(): ?string
    {
        return $this->hostedDomain;
    }
}
