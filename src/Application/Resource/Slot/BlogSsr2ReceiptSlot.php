<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Resource\Slot;

use Semitexa\Ssr\Attribute\AsSlotResource;
use Semitexa\Ssr\Application\Service\Http\Response\HtmlSlotResponse;

#[AsSlotResource(
    handle: 'demo_blog_ssr2',
    slot: 'ssr2_receipt',
    template: '@project-layouts-semitexa-demo/partials/blog-ssr2-quote.html.twig',
    deferred: true,
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/blog-ssr2-receipt.skeleton.html.twig',
)]
#[AsSlotResource(
    handle: 'demo_blog_ssr2',
    slot: 'ssr2_template_quote',
    template: '@project-layouts-semitexa-demo/partials/blog-ssr2-quote.html.twig',
    deferred: true,
    mode: 'template',
    skeletonTemplate: '@project-layouts-semitexa-demo/deferred/blog-ssr2-receipt.skeleton.html.twig',
)]
final class BlogSsr2ReceiptSlot extends HtmlSlotResponse
{
    /** @param array<string, string> $quote */
    public function withQuote(array $quote): static
    {
        return $this->with('quote', $quote);
    }
}
