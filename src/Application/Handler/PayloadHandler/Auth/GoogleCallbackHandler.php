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
use Semitexa\Demo\Application\Payload\Request\Auth\GoogleCallbackPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Payload\Session\GoogleSessionIdentityPayload;
use Semitexa\Demo\Application\Service\GoogleOAuthClient;

#[AsPayloadHandler(payload: GoogleCallbackPayload::class, resource: ResourceResponse::class)]
final class GoogleCallbackHandler implements TypedHandlerInterface
{
    private const string PROVIDER = 'google';

    #[InjectAsMutable]
    protected SessionInterface $session;

    #[InjectAsReadonly]
    protected GoogleOAuthClient $oauthClient;

    #[InjectAsReadonly]
    protected AuthSessionWriter $authWriter;

    public function handle(GoogleCallbackPayload $payload, ResourceResponse $resource): ResourceResponse
    {
        if (!isset($this->session)) {
            throw new \RuntimeException('Session service is required for Google OAuth callback.');
        }

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $expectedState = $segment->getState();
        $returnTo = $this->oauthClient->sanitizeReturnTo($segment->getReturnTo() ?? '/demo/rendering/deferred');

        if ($payload->getError() !== null) {
            $segment->clear();
            $segment->setReturnTo($returnTo);
            $segment->setLastError($payload->getError());
            $this->session->setPayload($segment);
            $resource->setRedirect('/demo/auth/google?google_error=' . rawurlencode($payload->getError()) . '&return_to=' . rawurlencode($returnTo));
            return $resource;
        }

        if ($payload->getCode() === null || $payload->getState() === null || $expectedState === null || !hash_equals($expectedState, $payload->getState())) {
            $segment->clear();
            $segment->setReturnTo($returnTo);
            $segment->setLastError('invalid_state');
            $this->session->setPayload($segment);
            $resource->setRedirect('/demo/auth/google?google_error=' . rawurlencode('invalid_state') . '&return_to=' . rawurlencode($returnTo));
            return $resource;
        }

        try {
            $token = $this->oauthClient->exchangeCodeForToken($payload->getCode());
            $accessToken = is_string($token['access_token'] ?? null) ? trim((string) $token['access_token']) : '';
            if ($accessToken === '') {
                throw new \RuntimeException('Google OAuth token response did not include an access token.');
            }

            $profile = $this->oauthClient->fetchUserInfo($accessToken);
            $subjectId = is_string($profile['sub'] ?? null) ? trim((string) $profile['sub']) : '';
            $email = is_string($profile['email'] ?? null) ? trim((string) $profile['email']) : '';
            $emailVerified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOL);
            $displayName = is_string($profile['name'] ?? null) && trim((string) $profile['name']) !== ''
                ? trim((string) $profile['name'])
                : ($email !== '' ? $email : 'Google Account');
            $pictureUrl = is_string($profile['picture'] ?? null) ? trim((string) $profile['picture']) : null;
            $hostedDomain = is_string($profile['hd'] ?? null) ? trim((string) $profile['hd']) : null;

            if ($subjectId === '' || $email === '') {
                throw new \RuntimeException('Google OAuth userinfo response is incomplete.');
            }

            if (!$emailVerified) {
                throw new \RuntimeException('Google account email must be verified.');
            }

            if (!$this->oauthClient->isAllowedDomain($hostedDomain, $email)) {
                throw new \RuntimeException('Google account is not in the allowed domain.');
            }
        } catch (\Throwable $e) {
            $segment->clear();
            $segment->setReturnTo($returnTo);
            $segment->setLastError($e->getMessage());
            $this->session->setPayload($segment);
            $resource->setRedirect('/demo/auth/google?google_error=' . rawurlencode($e->getMessage()) . '&return_to=' . rawurlencode($returnTo));
            return $resource;
        }

        $identity = new GoogleSessionIdentityPayload(
            subjectId: $subjectId,
            email: $email,
            displayName: $displayName,
            emailVerified: $emailVerified,
            pictureUrl: $pictureUrl !== '' ? $pictureUrl : null,
            hostedDomain: $hostedDomain !== '' ? $hostedDomain : null,
        );

        $segment->setState(null);
        $segment->setReturnTo($returnTo);
        $segment->setIdentity($identity);
        if ($segment->getDemoRole() === null) {
            $segment->setDemoRole('viewer');
        }
        $segment->clearLastError();
        $this->session->setPayload($segment);

        $role = $segment->getDemoRole();
        $role = is_string($role) ? trim($role) : '';
        $role = in_array($role, ['admin', 'editor', 'viewer'], true) ? $role : 'viewer';
        $segment->setDemoRole($role);
        $this->session->setPayload($segment);

        $this->authWriter->setAuthenticated(
            $this->session,
            'google:' . $subjectId . ':' . $role,
            self::PROVIDER,
        );

        $this->session->regenerate();

        $resource->setRedirect($returnTo);

        return $resource;
    }
}
