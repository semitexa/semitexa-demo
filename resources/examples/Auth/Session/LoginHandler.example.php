<?php

declare(strict_types=1);

namespace App\Handler\Auth;

use App\Auth\Session\BrowserSessionSegment;
use App\Payload\Auth\LoginPayload;
use App\Resource\Auth\LoginPageResource;
use App\User\UserRepositoryInterface;
use Semitexa\Authorization\Attributes\PublicEndpoint;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Exception\AuthenticationException;
use Semitexa\Core\Session\SessionInterface;

#[PublicEndpoint]
#[AsPayloadHandler(payload: LoginPayload::class, resource: LoginPageResource::class)]
final class LoginHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected UserRepositoryInterface $users;

    #[InjectAsMutable]
    protected SessionInterface $session;

    public function handle(LoginPayload $payload, LoginPageResource $resource): LoginPageResource
    {
        $user = $this->users->findByEmail($payload->getEmail());
        if ($user === null) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $user->assertPasswordMatches($payload->getPassword());

        $segment = $this->session->getPayload(BrowserSessionSegment::class);
        $segment->setUserId($user->getId());
        $segment->setDisplayName($user->getDisplayName());

        $this->session->setPayload($segment);
        $this->session->regenerate();

        return $resource->withSuccess('Signed in. The next request will restore this user from the session cookie.');
    }
}
