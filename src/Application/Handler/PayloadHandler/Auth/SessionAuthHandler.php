<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsMutable;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Core\Session\SessionInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\SessionAuthPayload;
use Semitexa\Demo\Application\Payload\Session\DemoSessionSegment;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: SessionAuthPayload::class, resource: DemoFeatureResource::class)]
final class SessionAuthHandler implements TypedHandlerInterface
{
    private const DEMO_USERS = [
        'admin'  => ['label' => 'Admin', 'permissions' => ['products.read', 'products.write', 'users.manage', 'orders.manage']],
        'editor' => ['label' => 'Editor', 'permissions' => ['products.read', 'products.write']],
        'viewer' => ['label' => 'Viewer', 'permissions' => ['products.read']],
    ];

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
        /** @var DemoSessionSegment $segment */
        $segment = $this->session?->getPayload(DemoSessionSegment::class) ?? new DemoSessionSegment();

        if ($payload->getAction() === 'login' && $payload->getRole() !== null) {
            $role = $payload->getRole();
            if (isset(self::DEMO_USERS[$role])) {
                $segment->setDemoRole($role);
                $segment->setDemoUsername(ucfirst($role) . ' User');
                $segment->incrementLoginCount();
                $this->session?->setPayload($segment);
                $this->session?->regenerate();
            }
        } elseif ($payload->getAction() === 'logout') {
            $segment->logout();
            $this->session?->setPayload($segment);
            $this->session?->regenerate();
        }

        $currentRole = $segment->getDemoRole();
        $currentUser = $segment->getDemoUsername();
        $loginCount = $segment->getLoginCount();
        $permissions = [];

        if ($currentRole !== null && isset(self::DEMO_USERS[$currentRole])) {
            $permissions = self::DEMO_USERS[$currentRole]['permissions'];
        } else {
            $permissions = [];
        }

        $explanation = $this->explanationProvider->getExplanation('auth', 'session') ?? [];

        $sourceCode = [
            'Session Segment' => $this->sourceCodeReader->readClassSource(DemoSessionSegment::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
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
            ->withSummary('Authenticate once per session — the framework stores identity and re-hydrates it on every request.')
            ->withEntryLine('Authenticate once per session — the framework stores identity and re-hydrates it on every request.')
            ->withHighlights(['SessionInterface', '#[SessionSegment]', 'AuthResult', '#[AsAuthHandler]'])
            ->withLearnMoreLabel('See the session lifecycle →')
            ->withDeepDiveLabel('How the auth pipeline works →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/session-auth.html.twig', [
                'stateTitle' => $currentRole !== null ? 'Authenticated session' : 'Guest session',
                'stateSummary' => $currentRole !== null
                    ? 'The session segment preserves the chosen role and permissions across requests.'
                    : 'Choose a demo role to simulate login and hydrate the request auth context.',
                'currentUser' => $currentUser,
                'currentRole' => $currentRole,
                'loginCount' => $loginCount,
                'permissions' => $permissions,
                'roles' => array_map(
                    static fn (string $key, array $meta): array => ['key' => $key, 'label' => $meta['label']],
                    array_keys(self::DEMO_USERS),
                    array_values(self::DEMO_USERS),
                ),
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
