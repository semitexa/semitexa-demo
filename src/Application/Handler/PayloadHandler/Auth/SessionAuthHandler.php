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

        if ($currentRole !== null && isset(self::DEMO_USERS[$currentRole])) {
            $permissions = self::DEMO_USERS[$currentRole]['permissions'];
            $permList = implode('', array_map(
                static fn(string $p) => '<li><code>' . htmlspecialchars($p) . '</code></li>',
                $permissions,
            ));
            $statusHtml = '<div class="auth-status auth-status--active">'
                . '<p>Logged in as <strong>' . htmlspecialchars($currentUser ?? '') . '</strong> '
                . '(<code>' . htmlspecialchars($currentRole) . '</code>)</p>'
                . '<p>Session logins: ' . $loginCount . '</p>'
                . '<p>Permissions:</p><ul>' . $permList . '</ul>'
                . '<form method="POST"><input type="hidden" name="action" value="logout">'
                . '<button type="submit" class="btn btn--secondary">Logout</button></form>'
                . '</div>';
        } else {
            $roleOptions = implode('', array_map(
                static fn(string $key, array $meta) =>
                    '<button type="submit" name="role" value="' . htmlspecialchars($key) . '" class="btn btn--primary">'
                    . 'Login as ' . htmlspecialchars($meta['label']) . '</button> ',
                array_keys(self::DEMO_USERS),
                array_values(self::DEMO_USERS),
            ));
            $statusHtml = '<div class="auth-status auth-status--guest">'
                . '<p>Not authenticated. Choose a demo role to simulate login:</p>'
                . '<form method="POST"><input type="hidden" name="action" value="login">'
                . $roleOptions . '</form></div>';
        }

        $resultPreview = '<div class="result-preview">' . $statusHtml . '</div>';

        $explanation = $this->explanationProvider->getExplanation('auth', 'session') ?? [];

        $sourceCode = [
            'Session Segment' => $this->sourceCodeReader->readClassSource(DemoSessionSegment::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Session Auth — Semitexa Demo')
            ->withSection('auth')
            ->withSlug('session')
            ->withTitle('Session Auth')
            ->withSummary('Authenticate once per session — the framework stores identity and re-hydrates it on every request.')
            ->withEntryLine('Authenticate once per session — the framework stores identity and re-hydrates it on every request.')
            ->withHighlights(['SessionInterface', '#[SessionSegment]', 'AuthResult', '#[AsAuthHandler]'])
            ->withLearnMoreLabel('See the session lifecycle →')
            ->withDeepDiveLabel('How the auth pipeline works →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
