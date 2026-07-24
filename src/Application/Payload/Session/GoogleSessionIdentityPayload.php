<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Session;

final class GoogleSessionIdentityPayload
{
    public function __construct(
        private readonly string $subjectId,
        private readonly string $email,
        private readonly string $displayName,
        private readonly bool $emailVerified = false,
        private readonly ?string $pictureUrl = null,
        private readonly ?string $hostedDomain = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $subjectId = is_string($data['subjectId'] ?? null) ? trim($data['subjectId']) : '';
        $email = is_string($data['email'] ?? null) ? trim($data['email']) : '';
        $displayName = is_string($data['displayName'] ?? null) ? trim($data['displayName']) : '';
        $pictureUrl = is_string($data['pictureUrl'] ?? null) && trim($data['pictureUrl']) !== '' ? trim($data['pictureUrl']) : null;
        $hostedDomain = is_string($data['hostedDomain'] ?? null) && trim($data['hostedDomain']) !== '' ? trim($data['hostedDomain']) : null;

        return new self(
            subjectId: $subjectId,
            email: $email,
            displayName: $displayName !== '' ? $displayName : ($email !== '' ? $email : 'Google Account'),
            emailVerified: filter_var($data['emailVerified'] ?? false, FILTER_VALIDATE_BOOL),
            pictureUrl: $pictureUrl,
            hostedDomain: $hostedDomain,
        );
    }

    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getEmailVerified(): bool
    {
        return $this->emailVerified;
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
