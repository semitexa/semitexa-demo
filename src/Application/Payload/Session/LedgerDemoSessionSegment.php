<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Session;

use Semitexa\Core\Session\Attribute\SessionSegment;

#[SessionSegment('demo_ledger')]
final class LedgerDemoSessionSegment
{
    private ?string $nonce = null;
    private ?string $lastTriggeredAt = null;

    public function getNonce(): ?string
    {
        return $this->nonce;
    }

    public function setNonce(?string $nonce): void
    {
        $nonce = $nonce !== null ? trim($nonce) : null;
        $this->nonce = $nonce !== '' ? $nonce : null;
    }

    public function issueNonce(): string
    {
        $this->nonce = bin2hex(random_bytes(16));

        return $this->nonce;
    }

    public function matchesNonce(?string $nonce): bool
    {
        if ($this->nonce === null || $nonce === null) {
            return false;
        }

        return hash_equals($this->nonce, $nonce);
    }

    public function rotateNonce(): string
    {
        return $this->issueNonce();
    }

    public function getLastTriggeredAt(): ?string
    {
        return $this->lastTriggeredAt;
    }

    public function markTriggered(string $timestamp): void
    {
        $this->lastTriggeredAt = $timestamp;
    }
}
