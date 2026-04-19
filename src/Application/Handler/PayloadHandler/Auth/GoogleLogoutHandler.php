<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Auth\Session\AuthSessionWriter;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\GoogleLogoutPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Service\GoogleOAuthClient;

#[AsPayloadHandler(payload: GoogleLogoutPayload::class, resource: ResourceResponse::class)]
final class GoogleLogoutHandler implements TypedHandlerInterface
{
    #[InjectAsMutable]
    protected ?SessionInterface $session = null;

    #[InjectAsReadonly]
    protected GoogleOAuthClient $oauthClient;

    #[InjectAsReadonly]
    protected AuthSessionWriter $authWriter;

    public function handle(GoogleLogoutPayload $payload, ResourceResponse $resource): ResourceResponse
    {
        if ($this->session === null) {
            throw new \RuntimeException('Session service is required for Google OAuth logout.');
        }

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $segment->clear();
        $this->session->setPayload($segment);

        $this->authWriter->clear($this->session);

        $this->session->regenerate();

        $returnTo = $this->oauthClient->sanitizeReturnTo($payload->getReturnTo() ?? '/demo/rendering/deferred');

        $resource->setRedirect($returnTo);
        return $resource;
    }
}
