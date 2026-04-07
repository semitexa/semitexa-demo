<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsMutable;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Auth\Context\AuthManager;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Auth\GooglePrincipal;
use Semitexa\Demo\Application\Payload\Request\Auth\SessionAuthPayload;
use Semitexa\Demo\Application\Payload\Session\GoogleAuthSessionSegment;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
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
    protected ?SessionInterface $session = null;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(SessionAuthPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        if ($this->session === null) {
            throw new \RuntimeException('Session service is required for SessionAuthHandler.');
        }

        $auth = AuthManager::getInstance();
        $user = $auth->getUser();
        $googleUser = $user instanceof GooglePrincipal ? $user : null;
        $isAuthenticated = !$auth->isGuest() && $googleUser !== null;

        /** @var GoogleAuthSessionSegment $segment */
        $segment = $this->session->getPayload(GoogleAuthSessionSegment::class);

        if (!$isAuthenticated) {
            $segment->setDemoRole(null);
            $this->session->setPayload($segment);
        } elseif (in_array($payload->getAction(), ['login', 'set_role'], true) && $payload->getRole() !== null) {
            $role = $this->normalizeRole($payload->getRole());
            $segment->setDemoRole($role);
            $this->session->setPayload($segment);
            $this->session->regenerate();
        }

        $currentRole = $segment->getDemoRole() ?? self::DEFAULT_ROLE;
        $roleMeta = self::ROLE_MATRIX[$currentRole] ?? self::ROLE_MATRIX[self::DEFAULT_ROLE];
        $permissions = $roleMeta['permissions'];

        $explanation = $this->explanationProvider->getExplanation('auth', 'session') ?? [];

        $sourceCode = [
            'Google Auth Segment' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Payload/Session/GoogleAuthSessionSegment.php'),
            'Google Login Handler' => $this->sourceCodeReader->readProjectRelativeSource('src/Application/Handler/PayloadHandler/Auth/GoogleCallbackHandler.php'),
            'Session Auth Handler' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Auth/Session/SessionAuthHandler.example.php'),
            'Session Role Matrix' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Auth/Session/SessionRoleMatrix.example.php'),
        ];

        return $resource
            ->pageTitle('Session Auth — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'session',
                'infoWhat' => $explanation['what'] ?? 'Authenticate once per session — the framework stores identity and re-hydrates it on every request.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('session')
            ->withTitle('Session Auth')
            ->withSummary('Google signs the user in; the session stores the selected demo role and re-hydrates it on every request.')
            ->withEntryLine('Google is the only login path; once signed in, the demo can switch roles to show how permissions change.')
            ->withHighlights(['Google OAuth', '#[SessionSegment]', 'AuthResult', '#[AsAuthHandler]'])
            ->withLearnMoreLabel('See the Google login flow →')
            ->withDeepDiveLabel('How role switching changes grants →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/session-auth.html.twig', [
                'stateTitle' => $isAuthenticated ? 'Google account connected' : 'Google sign-in required',
                'stateSummary' => $isAuthenticated
                    ? 'The session stores the authenticated Google identity and the selected demo role.'
                    : 'Sign in with Google first. The role selector is only available after authentication.',
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
                'authPageUrl' => '/demo/auth/google?local_test_bypass=1&return_to=' . rawurlencode('/demo/auth/session'),
                'googleLogoutUrl' => '/demo/auth/google/logout?return_to=' . rawurlencode('/demo/auth/session'),
                'roleChangeUrl' => '/demo/auth/session',
                'authRequiredMessage' => 'Authorization is required to access role switching in this demo.',
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }

    private function normalizeRole(?string $role): string
    {
        $role = $role !== null ? trim($role) : '';
        if ($role === '') {
            return self::DEFAULT_ROLE;
        }

        return array_key_exists($role, self::ROLE_MATRIX) ? $role : self::DEFAULT_ROLE;
    }
}
