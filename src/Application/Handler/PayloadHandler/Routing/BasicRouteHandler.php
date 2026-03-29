<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attributes\AsPayloadHandler;
use Semitexa\Core\Attributes\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Payload\Request\Routing\BasicRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoCatalogService;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BasicRoutePayload::class, resource: DemoFeatureResource::class)]
final class BasicRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoCatalogService $catalog;

    public function handle(BasicRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $explanation = $this->explanationProvider->getExplanation('routing', 'basic') ?? [];

        $sourceCode = [
            'Payload' => $this->sourceCodeReader->readClassSource(BasicRoutePayload::class),
            'Handler' => $this->sourceCodeReader->readClassSource(self::class),
        ];

        return $resource
            ->pageTitle('Basic Route — Semitexa Demo')
            ->withDemoShellContext([
                'navSections' => $this->catalog->getSections(),
                'featureTree' => $this->catalog->getFeatureTree(),
                'currentSection' => 'routing',
                'currentSlug' => 'basic',
                'infoWhat' => $explanation['what'] ?? 'Define a route with one attribute — no XML, no YAML, no config files.',
                'infoHow' => $explanation['how'] ?? null,
                'infoWhy' => $explanation['why'] ?? null,
                'infoKeywords' => $explanation['keywords'] ?? [],
            ])
            ->withSection('routing')
            ->withSlug('basic')
            ->withTitle('Basic Route')
            ->withSummary('Define a route with one attribute — no XML, no YAML, no config files.')
            ->withEntryLine('Define a route with one attribute — and even the path can move through .env without touching PHP code.')
            ->withHighlights(['#[AsPayload]', 'env::ROUTE_PATH', 'responseWith', 'TypedHandlerInterface'])
            ->withLearnMoreLabel('See the code →')
            ->withDeepDiveLabel('How route compilation works →')
            ->withSourceCode($sourceCode)
            ->withExplanation($explanation)
            ->withResultPreviewTemplate('@project-layouts-semitexa-demo/components/previews/route-snapshot.html.twig', [
                'eyebrow' => 'Route Discovery',
                'title' => 'Single attribute, live endpoint',
                'summary' => 'This page is reachable because the payload declared its route metadata directly in PHP, and that path can still be overridden from .env.',
                'method' => 'GET',
                'path' => '/demo/routing/basic',
                'status' => '200 OK',
                'facts' => ['#[AsPayload]', 'env:: path override', 'Typed handler', 'No central routes file'],
            ]);
    }
}
