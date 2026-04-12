<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Async;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoJsonResource;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/events/arena/launch',
    methods: ['POST'],
    responseWith: DemoJsonResource::class,
    produces: ['application/json'],
)]
class ExecutionArenaLaunchPayload
{
    protected ?string $mode = null;
    protected ?string $sessionId = null;

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(?string $mode): void
    {
        $this->mode = $mode !== null ? trim($mode) : null;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId !== null ? trim($sessionId) : null;
    }
}
