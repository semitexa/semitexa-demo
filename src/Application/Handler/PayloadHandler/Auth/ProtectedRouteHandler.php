<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Auth\ProtectedRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProtectedRoutePayload::class, resource: DemoFeatureResource::class)]
final class ProtectedRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(ProtectedRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'auth',
            slug: 'protected',
            entryLine: 'Add one attribute to any route and the framework enforces access — 403 returned automatically.',
            learnMoreLabel: 'See the guard attributes →',
            deepDiveLabel: 'How the guard chain resolves →',
            relatedSlugs: [],
            fallbackTitle: 'Protected Route',
            fallbackSummary: 'Add one attribute to any route and the framework enforces access — 403 returned automatically.',
            fallbackHighlights: ['#[RequiresPermission]', '#[PublicEndpoint]', 'guard chain', '403 response'],
            explanation: $this->explanationProvider->getExplanation('auth', 'protected') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Handler' => $this->sourceCodeReader->readClassSource(self::class),
            ])
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/permission-matrix.html.twig', [
                'eyebrow' => 'Route Guards',
                'title' => 'Access is enforced before the handler runs',
                'summary' => 'The guard chain decides whether the request should proceed, fail with 401/403, or bypass checks entirely for public endpoints.',
                'columns' => ['Scenario', 'Result'],
                'rows' => [
                    [['text' => 'Authenticated + correct permission'], ['text' => '200 OK', 'variant' => 'success']],
                    [['text' => 'Authenticated + missing permission'], ['text' => '403 Forbidden', 'variant' => 'error']],
                    [['text' => 'Not authenticated'], ['text' => '401 Unauthorized', 'variant' => 'warning']],
                    [['text' => '#[PublicEndpoint]', 'code' => true], ['text' => '200 OK (no auth check)', 'variant' => 'success']],
                ],
                'codeSnippet' => "// Protected: only admin can access\n#[RequiresPermission('users.manage')]\n#[AsPayload(path: '/admin/users', methods: ['GET'])]\nclass UserListPayload { ... }\n\n// Public: no auth required\n#[PublicEndpoint]\n#[AsPayload(path: '/demo/routing/basic', methods: ['GET'])]\nclass BasicRoutePayload { ... }",
            ]);
    }
}
