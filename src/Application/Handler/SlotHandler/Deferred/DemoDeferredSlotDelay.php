<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Core\Environment;
use Swoole\Coroutine;

final class DemoDeferredSlotDelay
{
    public static function sleepFor(string $slotId): void
    {
        if (Environment::getEnvValue('DEMO_DEFERRED_DELAY', '1') !== '1') {
            return;
        }

        $delayMs = match ($slotId) {
            'deferred_product_carousel' => 600,
            'deferred_chart_widget' => 1400,
            'deferred_search_filter' => 2200,
            'deferred_countdown' => 3000,
            'deferred_review_feed' => 3800,
            'deferred_notification' => 4600,
            default => 0,
        };

        if ($delayMs <= 0) {
            return;
        }

        if (class_exists(Coroutine::class, false) && Coroutine::getCid() > 0) {
            Coroutine::sleep($delayMs / 1000);
            return;
        }

        usleep($delayMs * 1000);
    }
}
