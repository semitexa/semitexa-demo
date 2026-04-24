<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Auth\Context\AuthManager;
use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Auth\SessionAuthPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoAuthMode;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SessionAuthPayload::class, resource: DemoFeatureResource::class)]
final class SessionAuthHandler implements TypedHandlerInterface
{
    private const ROLE_MATRIX = [
        'admin'  => ['label' => 'Admin', 'permissions' => ['products.read', 'products.write', 'users.manage', 'orders.manage', 'settings.manage']],
        'editor' => ['label' => 'Editor', 'permissions' => ['products.read', 'products.write']],
        'viewer' => ['label' => 'Viewer', 'permissions' => ['products.read']],
    ];

    private const DEFAULT_ROLE = 'viewer';

    #[InjectAsMutable]
    protected SessionInterface $session;

    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(SessionAuthPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        if (!isset($this->session)) {
            throw new \RuntimeException('Session service is required for SessionAuthHandler.');
        }

        $auth = AuthManager::getInstance();
        $user = $auth->getUser();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;
        $isAuthenticated = !$auth->isGuest() && $googleUser !== null;
        $isLocalLogin = DemoAuthMode::isLocalLoginEnabled();

        $segment = $this->applySessionMutations($payload, $isAuthenticated);
        $currentRole = $segment->getDemoRole() ?? self::DEFAULT_ROLE;
        $permissions = self::ROLE_MATRIX[$currentRole]['permissions'] ?? self::ROLE_MATRIX[self::DEFAULT_ROLE]['permissions'];

        $spec = new FeatureSpec(
            section: 'auth',
            slug: 'session',
            entryLine: $isLocalLogin
                ? 'Local sign-in is enabled in dev mode; once signed in, the demo can switch roles to show how permissions change.'
                : 'Google is the only login path; once signed in, the demo can switch roles to show how permissions change.',
            learnMoreLabel: 'See the Google login flow →',
            deepDiveLabel: 'How role switching changes grants →',
            relatedSlugs: [],
            fallbackTitle: 'Session Auth',
            fallbackSummary: 'Google signs the user in, then the session stores the selected demo role and re-hydrates it on every request.',
            fallbackHighlights: ['Google OAuth', '#[SessionSegment]', 'AuthResult', '#[AsAuthHandler]'],
            explanation: $this->explanationProvider->getExplanation('auth', 'session') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        $resource = $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Google Auth Segment' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Payload/Session/GoogleAuthSessionSegment.php'),
                'Google Login Handler' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Handler/PayloadHandler/Auth/GoogleCallbackHandler.php'),
                'Session Auth Handler' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Auth/Session/SessionAuthHandler.example.php'),
                'Session Role Matrix' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Auth/Session/SessionRoleMatrix.example.php'),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/session-auth.html.twig', $this->buildPreviewData(
                $isAuthenticated,
                $isLocalLogin,
                $googleUser,
                $currentRole,
                $permissions,
            ));

        // Local-login mode overrides the summary copy (and its SEO description) with a dev-mode variant.
        // The chained setter is the established pattern for post-projection overrides.
        if ($isLocalLogin) {
            $resource->withSummary('Local demo sign-in authorizes the user in dev mode; the session stores the selected demo role and re-hydrates it on every request.');
        }

        return $resource;
    }

    private function applySessionMutations(SessionAuthPayload $payload, bool $isAuthenticated): GoogleAuthSessionSegment
    {
        /** @var GoogleAuthSessionSegment $segment */
        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);

        if (!$isAuthenticated) {
            $segment->setDemoRole(null);
            $this->session->setPayload($segment);

            return $segment;
        }

        if (in_array($payload->getAction(), ['login', 'set_role'], true) && $payload->getRole() !== null) {
            $segment->setDemoRole($this->normalizeRole($payload->getRole()));
            $this->session->setPayload($segment);
            $this->session->regenerate();
        }

        return $segment;
    }

    private function normalizeRole(?string $role): string
    {
        $role = $role !== null ? trim($role) : '';
        if ($role === '') {
            return self::DEFAULT_ROLE;
        }

        return array_key_exists($role, self::ROLE_MATRIX) ? $role : self::DEFAULT_ROLE;
    }

    /**
     * @param list<string> $permissions
     * @return array<string, mixed>
     */
    private function buildPreviewData(
        bool $isAuthenticated,
        bool $isLocalLogin,
        ?GooglePrincipal $googleUser,
        string $currentRole,
        array $permissions,
    ): array {
        return [
            'stateTitle' => $isAuthenticated
                ? ($isLocalLogin ? 'Local account connected' : 'Google account connected')
                : ($isLocalLogin ? 'Local sign-in required' : 'Google sign-in required'),
            'stateSummary' => $isAuthenticated
                ? ($isLocalLogin
                    ? 'The session stores the authenticated local demo identity and the selected demo role.'
                    : 'The session stores the authenticated Google identity and the selected demo role.')
                : ($isLocalLogin
                    ? 'Sign in locally first. The role selector is only available after authentication.'
                    : 'Sign in with Google first. The role selector is only available after authentication.'),
            'isAuthenticated' => $isAuthenticated,
            'displayName' => $googleUser?->getDisplayName(),
            'email' => $googleUser?->getEmail(),
            'pictureUrl' => $googleUser?->getPictureUrl(),
            'hostedDomain' => $googleUser?->getHostedDomain(),
            'emailVerified' => $googleUser?->emailVerified ?? false,
            'currentRole' => $currentRole,
            'permissions' => $permissions,
            'roles' => array_map(
                static fn (string $key, array $meta): array => ['key' => $key, 'label' => $meta['label']],
                array_keys(self::ROLE_MATRIX),
                array_values(self::ROLE_MATRIX),
            ),
            'authPageUrl' => '/demo/auth/google?return_to=' . rawurlencode('/demo/auth/session'),
            'startUrl' => '/demo/auth/google/start?return_to=' . rawurlencode('/demo/auth/session'),
            'googleLogoutUrl' => '/demo/auth/google/logout?return_to=' . rawurlencode('/demo/auth/session'),
            'roleChangeUrl' => '/demo/auth/session',
            'authRequiredMessage' => $isLocalLogin
                ? 'Local sign-in is required to access role switching in this demo.'
                : 'Authorization is required to access role switching in this demo.',
            'authActionLabel' => DemoAuthMode::actionLabel(),
            'authSignedInLabel' => $isLocalLogin ? 'Signed in locally' : 'Signed in with Google',
        ];
    }
}
