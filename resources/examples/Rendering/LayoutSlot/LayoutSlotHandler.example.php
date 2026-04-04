<?php

declare(strict_types=1);

namespace App\Application\Handler\Rendering;

use App\Application\Payload\Page\DashboardPayload;
use App\Application\Resource\Layout\DashboardLayoutResource;
use App\Application\Resource\Slot\DashboardSidebarSlot;
use App\Domain\Navigation\DashboardNavigationBuilderInterface;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: DashboardPayload::class, resource: DashboardLayoutResource::class)]
final class LayoutSlotHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DashboardNavigationBuilderInterface $navigationBuilder;

    public function handle(DashboardPayload $payload, DashboardLayoutResource $resource): DashboardLayoutResource
    {
        return $resource->withSidebar(
            new DashboardSidebarSlot($this->navigationBuilder->items())
        );
    }
}
