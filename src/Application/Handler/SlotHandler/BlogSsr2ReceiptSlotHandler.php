<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler;

use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Demo\Application\Handler\SlotHandler\Deferred\DemoDeferredSlotDelay;
use Semitexa\Demo\Application\Resource\Slot\BlogSsr2ReceiptSlot;
use Semitexa\Demo\Application\Service\DemoSsrQuotePolicy;
use Semitexa\Ssr\Attribute\AsSlotHandler;
use Semitexa\Ssr\Domain\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: BlogSsr2ReceiptSlot::class)]
final class BlogSsr2ReceiptSlotHandler implements TypedSlotHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSsrQuotePolicy $quotePolicy;

    public function handle(object $slot): object
    {
        DemoDeferredSlotDelay::sleepFor('ssr2_receipt');

        /** @var BlogSsr2ReceiptSlot $slot */
        $context = $slot->getRenderContext();
        $delivery = (string) ($context['quote']['delivery'] ?? 'standard');

        return $slot->withQuote($this->quotePolicy->quote($delivery));
    }
}
