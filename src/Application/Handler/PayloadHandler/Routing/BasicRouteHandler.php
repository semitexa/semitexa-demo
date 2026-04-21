<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\BasicRoutePayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: BasicRoutePayload::class, resource: DemoFeatureResource::class)]
final class BasicRouteHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(BasicRoutePayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'basic',
            entryLine: 'One small payload class defines the endpoint. One small handler fills the response resource.',
            learnMoreLabel: 'See the minimal code →',
            deepDiveLabel: 'How route compilation works →',
            relatedSlugs: [],
            fallbackTitle: 'Basic Route',
            fallbackSummary: 'Define a route with one attribute — no XML, no YAML, no config files.',
            fallbackHighlights: ['#[AsPayload]', 'path', 'methods', 'responseWith'],
            explanation: $this->explanationProvider->getExplanation('routing', 'basic') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Payload' => $this->sourceCodeReader->readClassSource(BasicRoutePayload::class),
            ])
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
