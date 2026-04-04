<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Event;

use Semitexa\Core\Attribute\AsEvent;

#[AsEvent]
final class DemoNotificationEvent
{
    private string $message = '';
    private string $level = 'info';
    private ?string $targetUserId = null;

    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): void { $this->message = $message; }

    public function getLevel(): string { return $this->level; }
    public function setLevel(string $level): void { $this->level = $level; }

    public function getTargetUserId(): ?string { return $this->targetUserId; }
    public function setTargetUserId(?string $targetUserId): void { $this->targetUserId = $targetUserId; }
}
