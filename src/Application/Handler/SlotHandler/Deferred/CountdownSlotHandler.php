<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredCountdownSlot;
use Semitexa\Ssr\Attributes\AsSlotHandler;
use Semitexa\Ssr\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: DeferredCountdownSlot::class)]
final class CountdownSlotHandler implements TypedSlotHandlerInterface
{
    public function handle(object $slot): object
    {
        return $slot
            ->withDuration(30)
            ->withLabel('Demo timer')
            ->withInstanceId('demo-countdown');
    }
}
