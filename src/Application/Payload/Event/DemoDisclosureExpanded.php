<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Event;

use Semitexa\Core\Attributes\AsEvent;

#[AsEvent]
final class DemoDisclosureExpanded
{
    private string $targetId = '';
    private string $source = '';
    private string $elementTag = '';

    public function getTargetId(): string { return $this->targetId; }
    public function setTargetId(string $targetId): void { $this->targetId = $targetId; }

    public function getSource(): string { return $this->source; }
    public function setSource(string $source): void { $this->source = $source; }

    public function getElementTag(): string { return $this->elementTag; }
    public function setElementTag(string $elementTag): void { $this->elementTag = $elementTag; }
}
