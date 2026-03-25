<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attributes\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_blocks',
    slot: 'deferred_review_feed',
    template: '@project-layouts-semitexa-demo/deferred/review-feed.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/review-feed.skeleton.html.twig',
)]
final class DeferredReviewFeedSlot extends HtmlSlotResponse
{
    public function withReviews(array $reviews): static
    {
        return $this->with('reviews', $reviews);
    }
}
