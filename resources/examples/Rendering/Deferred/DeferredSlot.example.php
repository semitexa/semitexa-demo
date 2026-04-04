<?php

declare(strict_types=1);

namespace App\Application\Resource\Slot;

use Semitexa\Ssr\Attributes\AsSlotResource;

#[AsSlotResource(
    handle: 'dashboard',
    slot: 'dashboard.sidebar',
    template: '@project/dashboard/deferred-sidebar.html.twig',
    deferred: true,
)]
final class DeferredSidebarSlot
{
    /**
     * @param list<string> $items
     */
    public function __construct(
        public readonly string $headline,
        public readonly array $items,
    ) {}
}
