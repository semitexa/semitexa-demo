<?php

declare(strict_types=1);

namespace App\Application\Event;

use Semitexa\Core\Attribute\AsEvent;

#[AsEvent]
final class DemoDisclosureExpanded
{
    public function __construct(
        public readonly string $component,
        public readonly string $target,
    ) {}
}
