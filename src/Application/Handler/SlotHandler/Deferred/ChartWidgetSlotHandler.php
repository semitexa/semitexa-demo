<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\SlotHandler\Deferred;

use Semitexa\Demo\Application\Resource\Slot\Deferred\DeferredChartWidgetSlot;
use Semitexa\Ssr\Attribute\AsSlotHandler;
use Semitexa\Ssr\Contract\TypedSlotHandlerInterface;

#[AsSlotHandler(slot: DeferredChartWidgetSlot::class)]
final class ChartWidgetSlotHandler implements TypedSlotHandlerInterface
{
    public function handle(object $slot): object
    {
        return $slot
            ->withChartType('bar')
            ->withChartData([
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'values' => [42, 78, 55, 91, 63],
            ]);
    }
}
