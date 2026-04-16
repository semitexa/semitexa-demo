<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Auth;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Auth\ProtectedRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: ProtectedRoutePayload::class, resource: DemoFeatureResource::class)]
final class ProtectedRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(ProtectedRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'auth',
            'protected',
            'Protected Route',
            'Add one attribute to any route and the framework enforces access — 403 returned automatically.',
            ['#[RequiresPermission]', '#[PublicEndpoint]', 'guard chain', '403 response'],
        );
        $explanation = $this->explanationProvider->getExplanation('auth', 'protected') ?? [];

        $sourceCode = [
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'auth',
                'currentSlug' => 'protected',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('auth')
            ->withSlug('protected')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Add one attribute to any route and the framework enforces access — 403 returned automatically.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the guard attributes →')
            ->withDeepDiveLabel('How the guard chain resolves →')
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
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
