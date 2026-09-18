<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/events/sync',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    produces: ['text/html', 'application/json'],
)]
class SyncEventPayload
{
    protected ?string $trigger = null;

    public function getTrigger(): ?string { return $this->trigger; }
    public function setTrigger(?string $trigger): void { $this->trigger = $trigger; }
}
