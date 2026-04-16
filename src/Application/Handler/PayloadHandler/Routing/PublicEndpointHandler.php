<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\PublicEndpointPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PublicEndpointPayload::class, resource: DemoFeatureResource::class)]
final class PublicEndpointHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(PublicEndpointPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'routing',
            'public-endpoint',
            'Public Endpoint',
            'Every endpoint is private by default. #[PublicEndpoint] is the explicit opt-in for anonymous access.',
            ['#[PublicEndpoint]', 'default private', '401 Unauthorized', 'Authorizer'],
        );
        $explanation = $this->explanationProvider->getExplanation('routing', 'public-endpoint') ?? [];

        $sourceCode = [
            'Public Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PublicEndpoint/PublicCatalogPayload.example.php'),
            'Protected Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PublicEndpoint/ProtectedDashboardPayload.example.php'),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'public-endpoint',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('public-endpoint')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('Anonymous access is never accidental: without #[PublicEndpoint], Semitexa treats the route as protected.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the access contract →')
            ->withDeepDiveLabel('How the authorizer decides →')
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/permission-matrix.html.twig', [
                'eyebrow' => 'Security Default',
                'title' => 'Private unless you say otherwise',
                'summary' => 'The absence of #[PublicEndpoint] is what keeps routes closed to guests. Public access must be deliberate and visible in code.',
                'columns' => ['Route declaration', 'Guest request result'],
                'rows' => [
                    [['text' => 'No auth attribute at all'], ['text' => '401 Unauthorized', 'variant' => 'warning']],
                    [['text' => '#[PublicEndpoint]', 'code' => true], ['text' => '200 OK', 'variant' => 'success']],
                    [['text' => '#[PublicEndpoint] + #[RequiresPermission]', 'code' => true], ['text' => 'Boot-time exception', 'variant' => 'error']],
                    [['text' => 'Authenticated user on default-private route'], ['text' => '200 OK', 'variant' => 'success']],
                ],
                'codeSnippet' => "// Protected by default\n#[AsPayload(path: '/dashboard', methods: ['GET'])]\nclass DashboardPayload {}\n\n// Explicitly public\n#[PublicEndpoint]\n#[AsPayload(path: '/catalog', methods: ['GET'])]\nclass CatalogPayload {}",
            ])
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation);
    }
}
