<?php

declare(strict_types=1);

namespace App\Application\Event;

use Semitexa\Core\Attribute\AsEvent;

#[AsEvent]
final class DemoItemCreated
{
    public function __construct(
        public readonly string $itemId,
        public readonly string $itemName,
        public readonly string $section,
    ) {}
}
