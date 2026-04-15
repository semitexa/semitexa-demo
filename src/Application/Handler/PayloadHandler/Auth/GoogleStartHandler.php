<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Http\Response\ResourceResponse;
use Semitexa\Core\Request;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\GoogleStartPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Payload\Session\GoogleSessionIdentityPayload;
use Semitexa\Demo\Application\Service\DemoAuthMode;
use Semitexa\Demo\Application\Service\GoogleOAuthClient;

#[AsPayloadHandler(payload: GoogleStartPayload::class, resource: ResourceResponse::class)]
final class GoogleStartHandler implements TypedHandlerInterface
{
    #[InjectAsMutable]
    protected ?SessionInterface $session = null;

    protected ?Request $httpRequest = null;

    #[InjectAsReadonly]
    protected GoogleOAuthClient $oauthClient;

    public function handle(GoogleStartPayload $payload, ResourceResponse $resource): ResourceResponse
    {
        if ($this->session === null) {
            throw new \RuntimeException('Session service is required for Google OAuth start.');
        }

        $returnTo = $this->oauthClient->sanitizeReturnTo($payload->getReturnTo() ?? '/demo/rendering/deferred');
        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);

        if (DemoAuthMode::isLocalLoginEnabled()) {
            $this->completeLocalTestSignIn($segment, $returnTo);
            $this->session->setPayload($segment);
            $this->session->regenerate();
            $resource->setRedirect($returnTo);
            return $resource;
        }

        try {
            $state = $this->oauthClient->generateState();
            $segment->setState($state);
            $segment->setReturnTo($returnTo);
            $segment->clearLastError();
            $this->session->setPayload($segment);

            $resource->setRedirect($this->oauthClient->buildAuthorizationUrl($state));
        } catch (\Throwable $e) {
            $segment->clear();
            $segment->setReturnTo($returnTo);
            $segment->setLastError($e->getMessage());
            $this->session->setPayload($segment);
            $resource->setRedirect('/demo/auth/google?google_error=' . rawurlencode($e->getMessage()) . '&return_to=' . rawurlencode($returnTo));
        }

        return $resource;
    }

    private function completeLocalTestSignIn(GoogleAuthSessionSegment $segment, string $returnTo): void
    {
        $host = $this->getRequestHost();
        if ($host === '') {
            $host = 'semitexa.test';
        }

        $subjectId = 'local-demo-' . $this->normalizeSubjectSuffix($host);
        $email = 'demo@' . $host;

        $identity = new GoogleSessionIdentityPayload(
            subjectId: $subjectId,
            email: $email,
            displayName: 'Local Demo Account',
            emailVerified: true,
            pictureUrl: null,
            hostedDomain: $host,
        );

        $segment->clear();
        $segment->setReturnTo($returnTo);
        $segment->setIdentity($identity);
        $segment->setDemoRole('viewer');
        $segment->clearLastError();
    }

    private function getRequestHost(): string
    {
        if ($this->httpRequest !== null) {
            $host = trim((string) $this->httpRequest->getServer('HTTP_HOST'));
            if ($host === '') {
                $host = trim((string) $this->httpRequest->getHeader('Host'));
            }

            if ($host !== '') {
                $host = strtolower($host);
                $host = explode(':', $host, 2)[0];
                return trim($host);
            }
        }

        $serverHost = trim((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        if ($serverHost === '') {
            return '';
        }

        $serverHost = strtolower($serverHost);
        $serverHost = explode(':', $serverHost, 2)[0];

        return trim($serverHost);
    }

    private function normalizeSubjectSuffix(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9.-]+/', '-', $value);

        return trim((string) $value, '-');
    }
}
