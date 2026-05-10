<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Core\Attribute\AsPublicPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;

#[AsPublicPayload(
    path: '/demo/auth/session',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
class SessionAuthPayload
{
    protected ?string $role = null;
    protected ?string $action = null;

    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): void { $this->role = $role; }

    public function getAction(): ?string { return $this->action; }
    public function setAction(?string $action): void { $this->action = $action; }
}
