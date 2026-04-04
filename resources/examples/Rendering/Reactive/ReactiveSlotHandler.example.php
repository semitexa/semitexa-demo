<?php

declare(strict_types=1);

namespace App\Application\Handler\Rendering;

use App\Application\Payload\Page\ReactivePanelPayload;
use App\Application\Resource\Page\ReactivePanelResource;
use App\Application\Resource\Slot\ReactivePanelSlot;
use App\Domain\Reports\RealtimeReportBuilderInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: ReactivePanelPayload::class, resource: ReactivePanelResource::class)]
final class ReactiveSlotHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected RealtimeReportBuilderInterface $reportBuilder;

    public function handle(ReactivePanelPayload $payload, ReactivePanelResource $resource): ReactivePanelResource
    {
        return $resource->withReactiveSlot(
            new ReactivePanelSlot($this->reportBuilder->build())
        );
    }
}
