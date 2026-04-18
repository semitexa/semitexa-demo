<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Request;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Handler\Auth\GoogleSessionAuthHandler;
use Semitexa\Demo\Application\Payload\Request\Auth\GoogleAuthPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Payload\Session\GoogleSessionIdentityPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAuthMode;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\GoogleOAuthClient;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;
use Semitexa\Core\Session\SessionInterface;

#[AsPayloadHandler(payload: GoogleAuthPayload::class, resource: DemoFeatureResource::class)]
final class GoogleAuthHandler implements TypedHandlerInterface
{
    #[InjectAsMutable]
    protected ?SessionInterface $session = null;

    protected ?Request $httpRequest = null;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    #[InjectAsReadonly]
    protected GoogleOAuthClient $oauthClient;

    public function handle(GoogleAuthPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $auth = AuthManager::getInstance();
        $user = $auth->getUser();
        $isAuthenticated = !$auth->isGuest();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;
        $returnTo = $this->oauthClient->sanitizeReturnTo($payload->getReturnTo() ?? '/demo/rendering/deferred');
        $isConfigured = $this->oauthClient->isConfigured();
        $isLocalTestBypass = DemoAuthMode::isLocalLoginEnabled();
        $googleError = $this->resolveGoogleError($payload->getGoogleError());

        if ($payload->isLocalTestBypass() && $isLocalTestBypass) {
            return $this->completeLocalTestSignIn($resource, $returnTo);
        }

        $presentation = $this->documents->resolve(
            'auth',
            'google',
            'Google Authorization',
            'Authorization is required for demo SSE blocks that keep a long-lived backend connection open.',
            ['Authorization is required', 'Google Account', 'session-backed login', 'persistent SSE'],
        );
        $explanation = $this->explanationProvider->getExplanation('auth', 'google') ?? [];

        $sourceCode = [
            'Google Session Auth Handler' => $this->sourceCodeReader->readClassSource(GoogleSessionAuthHandler::class),
            'Google OAuth Client' => $this->sourceCodeReader->readClassSource(\Semitexa\Demo\Application\Service\GoogleOAuthClient::class),
            'Google Auth Segment' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Payload/Session/GoogleAuthSessionSegment.php'),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'google',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('google')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('This demo gates long-lived SSE surfaces behind a Google Account so the stream cannot be opened by anonymous traffic.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the authorization gate →')
            ->withDeepDiveLabel('Why the demo stream is protected →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/google-auth.html.twig', [
                'isAuthenticated' => $isAuthenticated,
                'displayName' => $googleUser?->getDisplayName() ?? ($user?->getId() ?? null),
                'email' => $googleUser?->getEmail(),
                'pictureUrl' => $googleUser?->getPictureUrl(),
                'hostedDomain' => $googleUser?->getHostedDomain(),
                'emailVerified' => $googleUser?->emailVerified ?? false,
                'returnTo' => $returnTo,
                'startUrl' => '/demo/auth/google/start?return_to=' . rawurlencode($returnTo),
                'logoutUrl' => '/demo/auth/google/logout?return_to=' . rawurlencode($returnTo),
                'authActionLabel' => DemoAuthMode::actionLabel(),
                'authProviderLabel' => DemoAuthMode::providerLabel(),
                'authSignedInLabel' => DemoAuthMode::signedInLabel(),
                'googleError' => $googleError,
                'authError' => $googleError,
                'authConfigured' => $isConfigured,
                'authBypassEnabled' => $isLocalTestBypass,
                'configurationMessage' => $isConfigured
                    ? null
                    : DemoAuthMode::configurationMessage(),
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }

    private function resolveGoogleError(?string $payloadError): ?string
    {
        $payloadError = $payloadError !== null ? trim($payloadError) : null;
        $payloadError = $payloadError !== '' ? $payloadError : null;

        if ($this->session === null) {
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

    private function getRequestHost(): string
    {
        if ($this->httpRequest !== null) {
            $host = trim((string) $this->httpRequest->getHost());

            if ($host !== '') {
                return strtolower($host);
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

    private function completeLocalTestSignIn(DemoFeatureResource $resource, string $returnTo): DemoFeatureResource
    {
        if ($this->session === null) {
            throw new \RuntimeException('Session service is required for local Google OAuth bypass.');
        }

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

        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);
        $segment->clear();
        $segment->setReturnTo($returnTo);
        $segment->setIdentity($identity);
        $segment->setDemoRole('viewer');
        $segment->clearLastError();
        $this->session->setPayload($segment);
        $this->session->set('_auth_user_id', 'google:' . $subjectId . ':' . $segment->getDemoRole());
        $this->session->regenerate();

        $resource->setRedirect($returnTo);
        return $resource;
    }

    private function normalizeSubjectSuffix(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9.-]+/', '-', $value);

        return trim((string) $value, '-');
    }
}
