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
        DemoDeferredSlotDelay::sleepFor('deferred_notification');

        return $slot
            ->withCount(3)
            ->withNotifications([
                ['level' => 'success', 'message' => 'This slot was delivered over SSE as a template payload.'],
                ['level' => 'info',    'message' => 'The browser rendered the published Twig template locally without a follow-up fallback request.'],
                ['level' => 'info',    'message' => 'The `__semitexa_hug` endpoint remains a rescue path for real failures only.'],
            ]);
    }
}
