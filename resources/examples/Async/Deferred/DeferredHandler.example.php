<?php

declare(strict_types=1);

namespace App\Application\Handler\Events;

use App\Application\Payload\Events\NotifyAfterResponsePayload;
use App\Application\Resource\Page\AsyncStatusResource;
use App\Domain\Notifications\NotificationSchedulerInterface;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;

#[AsPayloadHandler(payload: NotifyAfterResponsePayload::class, resource: AsyncStatusResource::class)]
final class DeferredHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected NotificationSchedulerInterface $scheduler;

    public function handle(NotifyAfterResponsePayload $payload, AsyncStatusResource $resource): AsyncStatusResource
    {
        $this->scheduler->scheduleDeferredNotification($payload->getUserId());

        return $resource->withMode('async');
    }
}
