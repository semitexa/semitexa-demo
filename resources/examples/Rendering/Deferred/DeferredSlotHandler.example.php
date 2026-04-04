<?php

declare(strict_types=1);

namespace App\Application\Handler\Rendering;

use App\Application\Payload\Page\DashboardPayload;
use App\Application\Resource\Page\DashboardPageResource;
use App\Application\Resource\Slot\DeferredSidebarSlot;
use App\Domain\Dashboard\DashboardMetricsInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: DashboardPayload::class, resource: DashboardPageResource::class)]
final class DeferredSlotHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DashboardMetricsInterface $metrics;

    public function handle(DashboardPayload $payload, DashboardPageResource $resource): DashboardPageResource
    {
        return $resource->withDeferredSlot(
            new DeferredSidebarSlot('Live metrics', $this->metrics->latest())
        );
    }
}
