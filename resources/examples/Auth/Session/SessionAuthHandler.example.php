<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Auth\GooglePrincipal;
use App\Auth\Session\GoogleAuthSessionSegment;
use App\Payload\Auth\SessionAuthPayload;
use App\Resource\Auth\SessionAuthResource;
use Semitexa\Authorization\Attribute\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Session\SessionInterface;

#[PublicEndpoint]
#[AsPayloadHandler(payload: SessionAuthPayload::class, resource: SessionAuthResource::class)]
final class SessionAuthHandler implements TypedHandlerInterface
{
    #[InjectAsMutable]
    protected SessionInterface $session;

    #[InjectAsReadonly]
    protected GooglePrincipal $auth;

    public function handle(SessionAuthPayload $payload, SessionAuthResource $resource): SessionAuthResource
    {
        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $segment->setDemoRole($payload->getRole() ?? 'viewer');
        $this->session->setPayload($segment);
        $this->session->regenerate();

        return $resource->withRole($segment->getDemoRole());
    }
}
