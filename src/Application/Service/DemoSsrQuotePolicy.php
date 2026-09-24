<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

/** One server-owned rule for both the initial page and its deferred receipt. */
#[AsService]
final class DemoSsrQuotePolicy
{
    /** @return array{delivery: string, deliveryLabel: string, subtotal: string, discount: string, shipping: string, total: string, rule: string} */
    public function quote(string $delivery): array
    {
        $delivery = $delivery === 'express' ? 'express' : 'standard';
        $subtotalCents = 16000;
        $discountCents = intdiv($subtotalCents, 10);
        $shippingCents = $delivery === 'express' ? 1200 : 0;

        return [
            'delivery' => $delivery,
            'deliveryLabel' => $delivery === 'express' ? 'Express delivery' : 'Standard delivery',
            'subtotal' => self::money($subtotalCents),
            'discount' => '-' . self::money($discountCents),
            'shipping' => $shippingCents === 0 ? 'Free' : self::money($shippingCents),
            'total' => self::money($subtotalCents - $discountCents + $shippingCents),
            'rule' => $delivery === 'express'
                ? 'The 10% member discount is applied before the $12 express delivery fee.'
                : 'The 10% member discount is applied and standard delivery is free for this order.',
        ];
    }

    private static function money(int $cents): string
    {
        return '$' . number_format($cents / 100, 2, '.', ',');
    }
}
