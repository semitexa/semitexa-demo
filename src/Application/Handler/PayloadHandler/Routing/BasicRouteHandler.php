<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\BasicRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoFeatureDocumentPresenter;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BasicRoutePayload::class, resource: DemoFeatureResource::class)]
final class BasicRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoFeatureDocumentPresenter $documents;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BasicRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $presentation = $this->documents->resolve(
            'routing',
            'basic',
            'Basic Route',
            'Define a route with one attribute — no XML, no YAML, no config files.',
            ['#[AsPayload]', 'path', 'methods', 'responseWith'],
        );
        $explanation = $this->explanationProvider->getExplanation('routing', 'basic') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(BasicRoutePayload::class),
        ];

        return $resource
            ->pageTitle($presentation->title . ' — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'basic',
                'infoWhat' => $explanation['what'] ?? $presentation->summary,
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('basic')
            ->withTitle($presentation->title)
            ->withSummary($presentation->summary)
            ->withEntryLine('One small payload class defines the endpoint. One small handler fills the response resource.')
            ->withHighlights($presentation->highlights)
            ->withDocumentBodyHtml($presentation->documentBodyHtml)
            ->withLearnMoreLabel('See the minimal code →')
            ->withDeepDiveLabel('How route compilation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/route-snapshot.html.twig', [
                'eyebrow' => 'Route Discovery',
                'title' => 'Single attribute, live endpoint',
                'summary' => 'This page exists because the payload declared the route directly in PHP and the handler only had to return the response resource.',
                'method' => 'GET',
                'path' => '/demo/routing/basic',
                'status' => '200 OK',
                'facts' => ['Payload owns route', 'One handler method', 'Typed response', 'No central routes file'],
            ]);
    }
}
