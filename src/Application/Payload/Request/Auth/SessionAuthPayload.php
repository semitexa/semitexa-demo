<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Payload\Request\Auth;

use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayload;
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
    summary: 'Google signs the user in, then the session stores the selected demo role and re-hydrates it on every request.',
    order: 2,
    highlights: ['Google OAuth', '#[SessionSegment]', 'AuthResult', '#[AsAuthHandler]'],
    entryLine: 'Google is the only login path; the session stores the selected role and re-hydrates it on every request.',
    learnMoreLabel: 'See the Google login flow →',
    deepDiveLabel: 'How role switching changes grants →',
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
