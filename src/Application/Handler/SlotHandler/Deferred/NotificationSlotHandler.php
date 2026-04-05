<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredNotificationSlot;
use Semitexa\Ssr\Attribute\AsSlotHandler;
use Semitexa\Ssr\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: DeferredNotificationSlot::class)]
final class NotificationSlotHandler implements TypedSlotHandlerInterface
{
    public function handle(object $slot): object
    {
        return $slot
            ->withCount(3)
            ->withNotifications([
                ['level' => 'info',    'message' => 'Deferred block arrived via SSE.'],
                ['level' => 'success', 'message' => 'All 6 blocks delivered.'],
                ['level' => 'info',    'message' => 'Live slots refresh every 5 s.'],
            ]);
    }
}
