<?php

declare(strict_types=1);

namespace App\Application\Resource\Slot;

use Semitexa\Ssr\Attributes\AsSlotResource;

#[AsSlotResource(slot: 'dashboard.sidebar', deferred: true)]
final class DeferredSidebarSlot
{
    public function __construct(
        public readonly string $headline,
        public readonly array $items,
    ) {}
}
