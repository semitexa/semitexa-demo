<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Event;

use Semitexa\Core\Attribute\AsEvent;

#[AsEvent]
final class DemoExecutionShowcaseAsyncRequested
{
    private string $runId = '';
    private string $sessionId = '';
    private string $requestedAt = '';

    public function getRunId(): string
    {
        return $this->runId;
    }

    public function setRunId(string $runId): void
    {
        $this->runId = $runId;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getRequestedAt(): string
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(string $requestedAt): void
    {
        $this->requestedAt = $requestedAt;
    }
}
