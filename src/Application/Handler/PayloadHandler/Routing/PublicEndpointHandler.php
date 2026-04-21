<?php

declare(strict_types=1);

namespace Semitexa\Demo\Application\Handler\PayloadHandler\Routing;

use Semitexa\Core\Attribute\AsPayloadHandler;
use Semitexa\Core\Attribute\InjectAsReadonly;
use Semitexa\Core\Contract\TypedHandlerInterface;
use Semitexa\Demo\Application\Feature\DemoFeaturePageProjector;
use Semitexa\Demo\Application\Feature\FeatureSpec;
use Semitexa\Demo\Application\Payload\Request\Routing\PublicEndpointPayload;
use Semitexa\Demo\Application\Resource\Response\DemoFeatureResource;
use Semitexa\Demo\Application\Service\DemoExplanationProvider;
use Semitexa\Demo\Application\Service\DemoSourceCodeReader;

#[AsPayloadHandler(payload: PublicEndpointPayload::class, resource: DemoFeatureResource::class)]
final class PublicEndpointHandler implements TypedHandlerInterface
{
    #[InjectAsReadonly]
    protected DemoFeaturePageProjector $projector;

    #[InjectAsReadonly]
    protected DemoExplanationProvider $explanationProvider;

    #[InjectAsReadonly]
    protected DemoSourceCodeReader $sourceCodeReader;

    public function handle(PublicEndpointPayload $payload, DemoFeatureResource $resource): DemoFeatureResource
    {
        $spec = new FeatureSpec(
            section: 'routing',
            slug: 'public-endpoint',
            entryLine: 'Anonymous access is never accidental: without #[PublicEndpoint], Semitexa treats the route as protected.',
            learnMoreLabel: 'See the access contract →',
            deepDiveLabel: 'How the authorizer decides →',
            relatedSlugs: [],
            fallbackTitle: 'Public Endpoint',
            fallbackSummary: 'Every endpoint is private by default. #[PublicEndpoint] is the explicit opt-in for anonymous access.',
            fallbackHighlights: ['#[PublicEndpoint]', 'default private', '401 Unauthorized', 'Authorizer'],
            explanation: $this->explanationProvider->getExplanation('routing', 'public-endpoint') ?? [],
            pageTitleSuffix: ' — Semitexa Demo',
        );

        return $this->projector->project($resource, $spec)
            ->withSourceCode([
                'Public Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PublicEndpoint/PublicCatalogPayload.example.php'),
                'Protected Payload' => $this->sourceCodeReader->readProjectRelativeSource('resources/examples/Routing/PublicEndpoint/ProtectedDashboardPayload.example.php'),
            ])
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
            ]);
    }
}
