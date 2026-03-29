<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attributes\AsPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Attributes\DemoFeature;

#[PublicEndpoint]
#[AsPayload(
    path: '/demo/auth/session',
    methods: ['GET', 'POST'],
    responseWith: DemoFeatureResource::class,
    produces: ['application/json', 'text/html'],
)]
#[DemoFeature(
    section: 'auth',
    title: 'Session Auth',
    slug: 'session',
    summary: 'Authenticate once per session — the framework stores identity and re-hydrates it on every request.',
    order: 1,
    highlights: ['SessionInterface', '#[SessionSegment]', 'AuthResult', '#[AsAuthHandler]'],
    entryLine: 'Authenticate once per session — the framework stores identity and re-hydrates it on every request.',
    learnMoreLabel: 'See the session lifecycle →',
    deepDiveLabel: 'How the auth pipeline works →',
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
