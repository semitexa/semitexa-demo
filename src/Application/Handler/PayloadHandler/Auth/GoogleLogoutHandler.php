<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\GoogleLogoutPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;

#[AsPayloadHandler(payload: GoogleLogoutPayload::class, resource: ResourceResponse::class)]
final class GoogleLogoutHandler implements TypedHandlerInterface
{
    #[InjectAsMutable]
    protected ?SessionInterface $session = null;

    public function handle(GoogleLogoutPayload $payload, ResourceResponse $resource): ResourceResponse
    {
        if ($this->session === null) {
            throw new \RuntimeException('Session service is required for Google OAuth logout.');
        }

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $segment->clear();
        $this->session->setPayload($segment);
        $this->session->remove('_auth_user_id');
        $this->session->regenerate();

        $returnTo = trim((string) ($payload->getReturnTo() ?? '/demo/rendering/deferred'));
        if ($returnTo === '' || !str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
            $returnTo = '/demo/rendering/deferred';
        }

        $resource->setRedirect($returnTo);
        return $resource;
    }
}
