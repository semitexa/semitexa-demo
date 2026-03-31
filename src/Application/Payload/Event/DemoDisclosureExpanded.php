<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Event;

use Semitexa\Core\Attributes\AsEvent;

#[AsEvent]
final class DemoDisclosureExpanded
{
    public function __construct(
        private readonly string $targetId,
        private readonly string $source,
        private readonly string $elementTag,
    ) {}

    public function getTargetId(): string { return $this->targetId; }
    public function getSource(): string { return $this->source; }
    public function getElementTag(): string { return $this->elementTag; }
}
