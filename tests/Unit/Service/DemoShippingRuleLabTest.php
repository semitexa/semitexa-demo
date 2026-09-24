<?php

declare(strict_types=1);

namespace Semitexa\Demo\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Semitexa\Demo\Application\Service\DemoShippingRuleLab;

final class DemoShippingRuleLabTest extends TestCase
{
    /** @return iterable<string, array{string, string, int, int, string, bool}> */
    public static function acceptanceCases(): iterable
    {
        yield 'corrected: below threshold pays delivery' => ['below', 'fixed', 1200, 1200, '$111.99', true];
        yield 'corrected: threshold itself is free' => ['boundary', 'fixed', 0, 0, '$100.00', true];
        yield 'corrected: above threshold is free' => ['above', 'fixed', 0, 0, '$100.01', true];
        yield 'faulty: below threshold still works' => ['below', 'buggy', 1200, 1200, '$111.99', true];
        yield 'faulty: boundary exposes the overcharge' => ['boundary', 'buggy', 1200, 0, '$112.00', false];
        yield 'faulty: above threshold still works' => ['above', 'buggy', 0, 0, '$100.01', true];
    }

    #[DataProvider('acceptanceCases')]
    public function test_lab_matches_independent_acceptance_cases(
        string $scenario,
        string $rule,
        int $actualShipping,
        int $expectedShipping,
        string $total,
        bool $matches,
    ): void {
        $result = (new DemoShippingRuleLab())->run($scenario, $rule);

        self::assertSame($actualShipping, $result['actualShippingCents']);
        self::assertSame($expectedShipping, $result['expectedShippingCents']);
        self::assertSame($total, $result['actualTotal']);
        self::assertSame($matches, $result['matches']);
    }

    public function test_unknown_inputs_return_the_documented_boundary_fixture(): void
    {
        $lab = new DemoShippingRuleLab();
        self::assertSame($lab->run('boundary', 'buggy'), $lab->run('unknown', 'unknown'));
    }
}
