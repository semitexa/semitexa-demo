<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredReviewFeedSlot;
use Semitexa\Ssr\Attribute\AsSlotHandler;
use Semitexa\Ssr\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: DeferredReviewFeedSlot::class)]
final class ReviewFeedSlotHandler implements TypedSlotHandlerInterface
{
    public function handle(object $slot): object
    {
        return $slot->withReviews([
            ['rating' => 5, 'body' => 'Blazing fast — the SSE approach is seamless.'],
            ['rating' => 4, 'body' => 'Deferred loading feels instant to the user.'],
            ['rating' => 5, 'body' => 'Skeleton screens make the wait painless.'],
        ]);
    }
}
