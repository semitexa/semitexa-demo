<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredSearchFilterSlot;
use Semitexa\Ssr\Attributes\AsSlotHandler;
use Semitexa\Ssr\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: DeferredSearchFilterSlot::class)]
final class SearchFilterSlotHandler implements TypedSlotHandlerInterface
{
    public function handle(object $slot): object
    {
        return $slot
            ->withCategories([
                ['slug' => 'electronics', 'name' => 'Electronics'],
                ['slug' => 'audio',       'name' => 'Audio'],
                ['slug' => 'peripherals', 'name' => 'Peripherals'],
            ])
            ->withPriceRange(0, 500);
    }
}
