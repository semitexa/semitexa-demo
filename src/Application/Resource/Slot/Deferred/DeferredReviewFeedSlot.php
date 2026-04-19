<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot\Deferred;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_deferred_blocks',
    slot: 'deferred_review_feed',
    template: '@project-layouts-semitexa-demo/deferred/review-feed.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/review-feed.skeleton.html.twig',
    mode: 'template',
    clientModules: ['@project-static-semitexa-demo/deferred/review-feed.js'],
)]
final class DeferredReviewFeedSlot extends HtmlSlotResponse
{
    public function withReviews(array $reviews): static
    {
        return $this->with('reviews', $reviews);
    }

    public function withReviewCount(int $reviewCount): static
    {
        return $this->with('reviewCount', $reviewCount);
    }
}
