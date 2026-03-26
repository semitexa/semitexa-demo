<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredProductCarouselSlot;
use Semitexa\Ssr\Attributes\AsSlotHandler;
use Semitexa\Ssr\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: DeferredProductCarouselSlot::class)]
final class ProductCarouselSlotHandler implements TypedSlotHandlerInterface
{
    public function handle(object $slot): object
    {
        return $slot->withProducts([
            ['name' => 'Wireless Headphones', 'price' => '79.99', 'status' => 'active'],
            ['name' => 'Mechanical Keyboard', 'price' => '129.00', 'status' => 'active'],
            ['name' => 'USB-C Hub',            'price' => '39.50', 'status' => 'active'],
            ['name' => 'Webcam HD',            'price' => '59.00', 'status' => 'sale'],
        ]);
    }
}
