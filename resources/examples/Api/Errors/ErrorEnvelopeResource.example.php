<?php

declare(strict_types=1);

namespace App\Application\Resource\Api;

final class ErrorEnvelopeResource
{
    /**
     * @param array<string, mixed> $context
     */
    public function fromError(string $type, string $message, array $context = []): self
    {
        return $this;
    }
}
