<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredReviewFeedSlot;
use Semitexa\Ssr\Attribute\AsSlotHandler;
use Semitexa\Ssr\Domain\Contract\TypedSlotHandlerInterface;

/** @implements TypedSlotHandlerInterface<DeferredReviewFeedSlot, DeferredReviewFeedSlot> */
#[AsSlotHandler(slot: DeferredReviewFeedSlot::class)]
final class ReviewFeedSlotHandler implements TypedSlotHandlerInterface
{
    /** How many stars a rating is drawn out of. */
    private const int SCALE = 5;

    public function handle(object $slot): object
    {
        DemoDeferredSlotDelay::sleepFor('deferred_review_feed');

        $rated = [
            ['rating' => 5, 'body' => 'Blazing fast — the SSE approach is seamless.'],
            ['rating' => 4, 'body' => 'Deferred loading feels instant to the user.'],
            ['rating' => 5, 'body' => 'Skeleton screens make the wait painless.'],
        ];

        // The star row is built here rather than in the template: this slot is
        // deferred, so its template also renders in the browser, where the Twig
        // subset has no range operator to count 1..5 with.
        /** @var list<array{rating: int, body: string, stars: list<bool>}> $reviews */
        $reviews = array_map(
            static fn (array $review): array => $review + [
                'stars' => array_map(
                    static fn (int $position): bool => $position <= $review['rating'],
                    range(1, self::SCALE),
                ),
            ],
            $rated,
        );

        return $slot
            ->withReviews($reviews);
    }
}
