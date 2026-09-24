<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Service;

use Semitexa\Core\Attribute\AsService;

/** Isolated teaching fixtures; never used to price a real order. */
#[AsService]
final class DemoShippingRuleLab
{
    // Expected delivery charges are explicit examples of the product requirement.
    private const CASES = [
        'below' => ['subtotal' => 9999, 'expectedShipping' => 1200, 'label' => 'Below the threshold'],
        'boundary' => ['subtotal' => 10000, 'expectedShipping' => 0, 'label' => 'Exactly at the threshold'],
        'above' => ['subtotal' => 10001, 'expectedShipping' => 0, 'label' => 'Above the threshold'],
    ];

    /** @return array<string, string|int|bool> */
    public function run(string $scenario, string $rule): array
    {
        $scenario = isset(self::CASES[$scenario]) ? $scenario : 'boundary';
        $rule = $rule === 'fixed' ? 'fixed' : 'buggy';
        $case = self::CASES[$scenario];
        $shippingCents = $this->shippingCents($case['subtotal'], $rule);

        return [
            'scenario' => $scenario,
            'scenarioLabel' => $case['label'],
            'rule' => $rule,
            'subtotalCents' => $case['subtotal'],
            'actualShippingCents' => $shippingCents,
            'expectedShippingCents' => $case['expectedShipping'],
            'subtotal' => self::money($case['subtotal']),
            'actualShipping' => self::money($shippingCents),
            'expectedShipping' => self::money($case['expectedShipping']),
            'actualTotal' => self::money($case['subtotal'] + $shippingCents),
            'expectedTotal' => self::money($case['subtotal'] + $case['expectedShipping']),
            'matches' => $shippingCents === $case['expectedShipping'],
            'condition' => $rule === 'fixed' ? '$subtotalCents >= 10000' : '$subtotalCents > 10000',
        ];
    }

    private function shippingCents(int $subtotalCents, string $rule): int
    {
        if ($rule === 'fixed') {
            return $subtotalCents >= 10000 ? 0 : 1200;
        }

        // Deliberately retained faulty fixture for the article's comparison.
        return $subtotalCents > 10000 ? 0 : 1200;
    }

    private static function money(int $cents): string
    {
        return '$' . number_format($cents / 100, 2, '.', ',');
    }
}
