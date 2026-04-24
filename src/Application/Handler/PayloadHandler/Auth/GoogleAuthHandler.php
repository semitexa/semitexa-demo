<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Auth\Session\AuthSessionWriter;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Handler\Auth\GoogleSessionAuthHandler;
use Semitexa\Demo\Application\Payload\Request\Auth\GoogleAuthPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Payload\Session\GoogleSessionIdentityPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAuthMode;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Demo\Application\Service\GoogleOAuthClient;

#[AsPayloadHandler(payload: GoogleAuthPayload::class, resource: DemoFeatureResource::class)]
final class GoogleAuthHandler implements TypedHandlerInterface
{
    private const PROVIDER = 'google';

    #[InjectAsMutable]
    protected SessionInterface $session;

    protected ?Request $httpRequest = null;

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected GoogleOAuthClient $oauthClient;

    #[InjectAsReadonly]
    protected AuthSessionWriter $authWriter;

    public function handle(GoogleAuthPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $returnTo = $this->oauthClient->sanitizeReturnTo($payload->getReturnTo() ?? '/demo/rendering/deferred');

        // Local-test bypass short-circuits page rendering with a sign-in redirect.
        if ($payload->isLocalTestBypass() && DemoAuthMode::isLocalLoginEnabled()) {
            return $this->completeLocalTestSignIn($resource, $returnTo);
        }

        $spec = new FeatureSpec(
            section: 'auth',
            slug: 'google',
            entryLine: 'This demo gates long-lived SSE surfaces behind a Google Account so the stream cannot be opened by anonymous traffic.',
            learnMoreLabel: 'See the authorization gate →',
            deepDiveLabel: 'Why the demo stream is protected →',
            relatedSlugs: [],
            fallbackTitle: 'Google Authorization',
            fallbackSummary: 'Authorization is required for demo SSE blocks that keep a long-lived backend connection open.',
            fallbackHighlights: ['Authorization is required', 'Google Account', 'session-backed login', 'persistent SSE'],
            explanation: $this->explanationProvider->getExplanation('auth', 'google') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Google Session Auth Handler' => $this->sourceCodeReader->readClassSource(GoogleSessionAuthHandler::class),
                'Google OAuth Client' => $this->sourceCodeReader->readClassSource(GoogleOAuthClient::class),
                'Google Auth Segment' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Payload/Session/GoogleAuthSessionSegment.php'),
            ])
            ->withResultPreviewTemplate(
                '@project-layouts-semitexa-demo/components/previews/google-auth.html.twig',
                $this->buildPreviewData($returnTo, $payload->getGoogleError()),
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPreviewData(string $returnTo, ?string $payloadError): array
    {
        $user = AuthManager::getInstance()->getUser();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;
        $isAuthenticated = !AuthManager::getInstance()->isGuest();
        $isConfigured = $this->oauthClient->isConfigured();
        $bypassEnabled = DemoAuthMode::isLocalLoginEnabled();
        $googleError = $this->resolveGoogleError($payloadError);

        return [
            'isAuthenticated' => $isAuthenticated,
            'displayName' => $googleUser?->getDisplayName() ?? ($user?->getId() ?? null),
            'email' => $googleUser?->getEmail(),
            'pictureUrl' => $googleUser?->getPictureUrl(),
            'hostedDomain' => $googleUser?->getHostedDomain(),
            'emailVerified' => $googleUser?->getEmailVerified() ?? false,
            'returnTo' => $returnTo,
            'startUrl' => '/demo/auth/google/start?return_to=' . rawurlencode($returnTo),
            'logoutUrl' => '/demo/auth/google/logout?return_to=' . rawurlencode($returnTo),
            'authActionLabel' => DemoAuthMode::actionLabel(),
            'authProviderLabel' => DemoAuthMode::providerLabel(),
            'authSignedInLabel' => DemoAuthMode::signedInLabel(),
            'googleError' => $googleError,
            'authError' => $googleError,
            'authConfigured' => $isConfigured,
            'authBypassEnabled' => $bypassEnabled,
            'configurationMessage' => $isConfigured ? null : DemoAuthMode::configurationMessage(),
        ];
    }

    private function resolveGoogleError(?string $payloadError): ?string
    {
        $payloadError = $payloadError !== null ? trim($payloadError) : null;
        $payloadError = $payloadError !== '' ? $payloadError : null;

        if (!isset($this->session)) {
            return $payloadError;
        }

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $sessionError = $segment->getLastError();

        if ($sessionError !== null) {
            $segment->clearLastError();
            $this->session->setPayload($segment);
        }

        return $payloadError ?? $sessionError;
    }

    private function completeLocalTestSignIn(DemoFeatureResource $resource, string $returnTo): DemoFeatureResource
    {
        if (!isset($this->session)) {
            throw new \RuntimeException('Session service is required for local Google OAuth bypass.');
        }

        $host = $this->getRequestHost() ?: 'semitexa.test';
        $subjectId = 'local-demo-' . $this->normalizeSubjectSuffix($host);

        $identity = new GoogleSessionIdentityPayload(
            subjectId: $subjectId,
            email: 'demo@' . $host,
            displayName: 'Local Demo Account',
            emailVerified: true,
            pictureUrl: null,
            hostedDomain: $host,
        );

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $segment->clear();
        $segment->setReturnTo($returnTo);
        $segment->setIdentity($identity);
        $segment->setDemoRole('viewer');
        $segment->clearLastError();
        $this->session->setPayload($segment);
        $this->authWriter->setAuthenticated(
            $this->session,
            'google:' . $subjectId . ':' . $segment->getDemoRole(),
            self::PROVIDER,
        );
        $this->session->regenerate();

        $resource->setRedirect($returnTo);
        return $resource;
    }

    private function getRequestHost(): string
    {
        if ($this->httpRequest !== null) {
            $host = $this->normalizeHost((string) $this->httpRequest->getHost());
            if ($host !== '') {
                return $host;
            }
        }

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';

        return $this->normalizeHost(is_string($host) ? $host : '');
    }

    private function normalizeSubjectSuffix(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9.-]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    private function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host === '') {
            return '';
        }

        if (str_starts_with($host, '[')) {
            $end = strpos($host, ']');

            return $end === false ? trim($host, '[]') : substr($host, 1, $end - 1);
        }

        return trim(explode(':', $host, 2)[0]);
    }
}
