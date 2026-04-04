<?php

declare(strict_types=1);

namespace App\Application\Handler\Events;

use App\Application\Event\DemoItemCreated;
use App\Application\Payload\Events\CreateDemoItemPayload;
use App\Application\Resource\Page\DemoItemResource;
use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Event\EventDispatcherInterface;

#[AsPayloadHandler(payload: CreateDemoItemPayload::class, resource: DemoItemResource::class)]
final class SyncEventHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function handle(CreateDemoItemPayload $payload, DemoItemResource $resource): DemoItemResource
    {
        $event = new DemoItemCreated($payload->getId(), $payload->getName(), 'catalog');
        if ($this->eventDispatcher === null) {
            return $resource->withStatus('not_dispatched');
        }

        $this->eventDispatcher->dispatch($event);
        return $resource->withStatus('dispatched');
    }
}
