<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\ProtectedRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProtectedRoutePayload::class, resource: DemoFeatureResource::class)]
final class ProtectedRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    public function handle(ProtectedRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $resultPreview = '<div class="result-preview">'
            . '<p>Add <code>#[RequiresPermission]</code> to any payload and the framework enforces it before your handler runs.</p>'
            . '<table class="data-table">'
            . '<thead><tr><th>Scenario</th><th>Result</th></tr></thead>'
            . '<tbody>'
            . '<tr><td>Authenticated + correct permission</td><td><span class="badge badge--active">200 OK</span></td></tr>'
            . '<tr><td>Authenticated + missing permission</td><td><span class="badge badge--error">403 Forbidden</span></td></tr>'
            . '<tr><td>Not authenticated</td><td><span class="badge badge--warning">401 Unauthorized</span></td></tr>'
            . '<tr><td><code>#[PublicEndpoint]</code></td><td><span class="badge badge--active">200 OK (no auth check)</span></td></tr>'
            . '</tbody></table>'
            . '<pre class="code-inline">'
            . htmlspecialchars(
                "// Protected: only admin can access\n"
                . "#[RequiresPermission('users.manage')]\n"
                . "#[AsPayload(path: '/admin/users', methods: ['GET'])]\n"
                . "class UserListPayload { ... }\n\n"
                . "// Public: no auth required\n"
                . "#[PublicEndpoint]\n"
                . "#[AsPayload(path: '/demo/routing/basic', methods: ['GET'])]\n"
                . "class BasicRoutePayload { ... }"
            )
            . '</pre>'
            . '</div>';

        $explanation = $this->explanationProvider->getExplanation('auth', 'protected') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Protected Route — Semitexa Demo')
            ->withSection('auth')
            ->withSlug('protected')
            ->withTitle('Protected Route')
            ->withSummary('Add one attribute to any route and the framework enforces access — 403 returned automatically.')
            ->withEntryLine('Add one attribute to any route and the framework enforces access — 403 returned automatically.')
            ->withHighlights(['#[RequiresPermission]', '#[PublicEndpoint]', 'guard chain', '403 response'])
            ->withLearnMoreLabel('See the guard attributes →')
            ->withDeepDiveLabel('How the guard chain resolves →')
            ->withResultPreview($resultPreview)
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
